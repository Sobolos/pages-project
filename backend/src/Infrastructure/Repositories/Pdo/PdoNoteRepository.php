<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Note;
use App\Domain\Interfaces\Repositories\NoteRepositoryInterface;
use App\Infrastructure\Mappers\NoteMapper;

class PdoNoteRepository extends PdoRepository implements NoteRepositoryInterface
{
    private NoteMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new NoteMapper();
    }

    public function findById(int $id): ?Note
    {
        if ($this->mapper->getFromMap(Note::class, $id)) {
            return $this->mapper->getFromMap(Note::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM notes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $note = $this->mapper->toEntity($row);
        $this->mapper->addToMap($note, $id);
        return $note;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM notes WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['book_id'])) {
            $query .= ' AND book_id = :book_id';
            $params['book_id'] = $filters['book_id'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND content ILIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['sort_by'])) {
            $sortField = in_array($filters['sort_by'], ['created_at', 'content']) ? $filters['sort_by'] : 'created_at';
            $sortOrder = ($filters['sort_order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY $sortField $sortOrder";
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        $query .= ' LIMIT :per_page OFFSET :offset';
        $params['per_page'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $notes = [];
        foreach ($rows as $row) {
            $note = $this->mapper->toEntity($row);
            $notes[] = $note;
            $this->mapper->addToMap($note, $row['id']);
        }

        return $notes;
    }

    public function save(object $entity): void
    {
        if (!$entity instanceof Note) {
            throw new \InvalidArgumentException('Entity must be an instance of Note');
        }

        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            $updateData = array_diff_key($data, ['created_at' => true]);
            $stmt = $this->pdo->prepare(
                'UPDATE notes SET book_id = :book_id, user_id = :user_id, content = :content, ' .
                'updated_at = :updated_at WHERE id = :id'
            );
            $stmt->execute($updateData);
        } else {
            $insertData = array_diff_key($data, ['id' => true]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO notes (book_id, user_id, content, created_at, updated_at) ' .
                'VALUES (:book_id, :user_id, :content, :created_at, :updated_at)'
            );
            $stmt->execute($insertData);
            if (!$entity->getId()) {
                $data['id'] = (int)$this->pdo->lastInsertId();
                $this->mapper->addToMap($this->mapper->toEntity($data), $data['id']);
            }
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM notes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}