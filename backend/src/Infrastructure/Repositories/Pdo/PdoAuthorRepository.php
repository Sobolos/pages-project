<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Author;
use App\Infrastructure\Mappers\AuthorMapper;
use App\Infrastructure\Repositories\Interfaces\AuthorRepositoryInterface;

class PdoAuthorRepository extends PdoRepository implements AuthorRepositoryInterface
{
    private AuthorMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new AuthorMapper();
    }

    public function findById(int $id): ?Author
    {
        if ($this->mapper->getFromMap(Author::class, $id)) {
            return $this->mapper->getFromMap(Author::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM authors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findByBookId(int $bookId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.* FROM authors a JOIN book_authors ba ON a.id = ba.author_id WHERE ba.book_id = :book_id'
        );
        $stmt->execute(['book_id' => $bookId]);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM authors WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['name'])) {
            $query .= ' AND name = :name';
            $params['name'] = $filters['name'];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function save(object $entity): void
    {
        if (!$entity instanceof Author) {
            throw new \InvalidArgumentException('Entity must be an instance of Author');
        }

        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            unset($data['created_at']);
            $stmt = $this->pdo->prepare(
                'UPDATE authors SET name = :name, user_id = :user_id, updated_at = :updated_at WHERE id = :id'
            );
        } else {
            unset($data['id']);
            $stmt = $this->pdo->prepare(
                'INSERT INTO authors (name, user_id, created_at, updated_at) ' .
                'VALUES (:name, :user_id, :created_at, :updated_at)'
            );
        }
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM authors WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param Author[] $authors
     * @param $userId
     * @return void
     */
    public function batchCreate(array $authors, $userId): void
    {
        $placeholders = [];
        $values = [];

        foreach ($authors as $author) {
            $placeholders[] = '(?, ?, ?)';
            $values[] = $userId;
            $values[] = $author->getName();
            $values[] = $author->getCreatedAt()->format('Y-m-d H:i:s');
        }

        $sql = 'INSERT INTO authors (user_id, name, created_at) VALUES ' . implode(', ', $placeholders) .
            ' ON CONFLICT (user_id, name) DO NOTHING RETURNING id, name';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
    }

    public function findByNameBatch(int $userId, array $names): array
    {
        if (empty($names)) {
            return [];
        }

        // Уникальные имена
        $uniqueNames = array_unique(array_map('trim', $names));
        $placeholders = str_repeat('?,', count($uniqueNames) - 1) . '?';

        $stmt = $this->pdo->prepare(
            "SELECT id, user_id, name, created_at, updated_at FROM authors WHERE user_id = ? AND name IN ($placeholders)"
        );

        $stmt->execute(array_merge([$userId], $uniqueNames));

        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }
}