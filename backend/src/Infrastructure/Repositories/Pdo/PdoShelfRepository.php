<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Shelf;
use App\Infrastructure\Mappers\ShelfMapper;
use App\Infrastructure\Repositories\Interfaces\ShelfRepositoryInterface;

class PdoShelfRepository extends PdoRepository implements ShelfRepositoryInterface
{
    private ShelfMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new ShelfMapper();
    }

    public function findById(int $id): ?Shelf
    {
        if ($this->mapper->getFromMap(Shelf::class, $id)) {
            return $this->mapper->getFromMap(Shelf::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM shelves WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM shelves WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function save(object $entity): void
    {
        if (!$entity instanceof Shelf) {
            throw new \InvalidArgumentException('Entity must be an instance of Shelf');
        }

        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            unset($data['created_at']);
            $stmt = $this->pdo->prepare(
                'UPDATE shelves SET name = :name, user_id = :user_id, updated_at = :updated_at WHERE id = :id'
            );
        } else {
            unset($data['id']);
            $stmt = $this->pdo->prepare(
                'INSERT INTO shelves (name, user_id, created_at, updated_at) ' .
                'VALUES (:name, :user_id, :created_at, :updated_at)'
            );
        }
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM shelves WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}