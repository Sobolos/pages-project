<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Status;
use App\Infrastructure\Mappers\StatusMapper;
use App\Infrastructure\Repositories\Interfaces\StatusRepositoryInterface;

class PdoStatusRepository extends PdoRepository implements StatusRepositoryInterface
{
    private StatusMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new StatusMapper();
    }

    public function findById(int $id): ?Status
    {
        if ($this->mapper->getFromMap(Status::class, $id)) {
            return $this->mapper->getFromMap(Status::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM statuses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM statuses WHERE 1=1';
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
        if (!$entity instanceof Status) {
            throw new \InvalidArgumentException('Entity must be an instance of Status');
        }

        $data = $this->mapper->toArray($entity);

        if ($this->findById($entity->getId())) {
            unset($data['created_at']);
            $stmt = $this->pdo->prepare(
                'UPDATE statuses SET name = :name, user_id = :user_id, color = :color, ' .
                'hide_from_agile = :hide_from_agile, position = :position, updated_at = :updated_at WHERE id = :id'
            );
        } else {
            unset($data['id']);
            $stmt = $this->pdo->prepare(
                'INSERT INTO statuses (name, user_id, color, hide_from_agile, position, created_at, updated_at) ' .
                'VALUES (:name, :user_id, :color, :hide_from_agile, :position, :created_at, :updated_at)'
            );
        }
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM statuses WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function getStatusNameById(int $statusId, int $userId): ?Status
    {
        $stmt = $this->pdo->prepare('SELECT * FROM statuses WHERE id = :id AND user_id = :userId');
        $stmt->execute(['id' => $statusId, 'userId' => $userId]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    /**
     * @param Status[] $statuses
     * @return void
     */
    public function batchUpdate(array $statuses): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('
                UPDATE statuses 
                SET position = ?, updated_at = ? 
                WHERE id = ? AND user_id = ?
            ');

            foreach ($statuses as $status) {
                $stmt->execute([
                    $status->getPosition(),
                    $status->getUpdatedAt()->format('Y-m-d H:i:s'),
                    $status->getId(),
                    $status->getUserId()
                ]);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollback();
        }
    }
}