<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\HistoryEntry;
use App\Infrastructure\Mappers\HistoryMapper;
use App\Infrastructure\Repositories\Interfaces\HistoryRepositoryInterface;

class PdoHistoryRepository extends PdoRepository implements HistoryRepositoryInterface
{
    private HistoryMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new HistoryMapper();
    }

    public function findById(int $id): ?HistoryEntry
    {
        if ($this->mapper->getFromMap(HistoryEntry::class, $id)) {
            return $this->mapper->getFromMap(HistoryEntry::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM history WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $entry = $this->mapper->toEntity($row);
        $this->mapper->addToMap($entry, $id);
        return $entry;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM history WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['event_type'])) {
            $query .= ' AND event_type IN (' . implode(',', array_map(fn($i) => ":event_type_$i", array_keys($filters['event_type']))) . ')';
            foreach ($filters['event_type'] as $i => $type) {
                $params["event_type_$i"] = $type;
            }
        }

        if (isset($filters['sort_by'])) {
            $sortField = in_array($filters['sort_by'], ['created_at']) ? $filters['sort_by'] : 'created_at';
            $sortOrder = ($filters['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
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

        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->mapper->toEntity($row);
            $entries[] = $entry;
            $this->mapper->addToMap($entry, $row['id']);
        }

        return $entries;
    }

    public function save(HistoryEntry $entity): void
    {
        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            $updateData = array_diff_key($data, ['created_at' => true]);
            $stmt = $this->pdo->prepare(
                'UPDATE history SET user_id = :user_id, event_type = :event_type, ' .
                'message = :message WHERE id = :id'
            );
            $stmt->execute($updateData);
        } else {
            $insertData = array_diff_key($data, ['id' => true]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO history (user_id, event_type, book_id, note_id, quote_id, message, created_at) ' .
                'VALUES (:user_id, :event_type, :book_id, :note_id, :quote_id, :message, :created_at)'
            );
            $stmt->execute($insertData);
            if (!$entity->getId()) {
                $data['id'] = (int)$this->pdo->lastInsertId();
                $this->mapper->addToMap($this->mapper->toEntity($data), $data['id']);
            }
        }
    }
}