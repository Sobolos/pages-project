<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\DiaryEntry;
use App\Domain\Interfaces\Repositories\DiaryRepositoryInterface;
use App\Infrastructure\Mappers\DiaryMapper;

class PdoDiaryRepository extends PdoRepository implements DiaryRepositoryInterface
{
    private DiaryMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new DiaryMapper();
    }

    public function findById(int $id): ?DiaryEntry
    {
        if ($this->mapper->getFromMap(DiaryEntry::class, $id)) {
            return $this->mapper->getFromMap(DiaryEntry::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM diary_mapper WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM diary_mapper WHERE 1=1';
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

    public function save(DiaryEntry $entity): void
    {
        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            unset($data['created_at']);
            $stmt = $this->pdo->prepare(
                'UPDATE diary_mapper SET name = :name, user_id = :user_id, updated_at = :updated_at WHERE id = :id'
            );
        } else {
            unset($data['id']);
            $stmt = $this->pdo->prepare(
                'INSERT INTO diary_mapper (name, user_id, created_at, updated_at) ' .
                'VALUES (:name, :user_id, :created_at, :updated_at)'
            );
        }
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM diary_mapper WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}