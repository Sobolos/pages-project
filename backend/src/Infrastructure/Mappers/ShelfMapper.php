<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Shelf;

class ShelfMapper extends BaseMapper
{
    public function toEntity(array $row): Shelf
    {
        if ($this->getFromMap(Shelf::class, $row['id'])) {
            return $this->getFromMap(Shelf::class, $row['id']);
        }

        $entity = new Shelf(
            id: (int)$row['id'],
            name: $row['name'],
            userId: (int)$row['user_id'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Shelf $shelf): array
    {
        return [
            'id' => $shelf->getId(),
            'name' => $shelf->getName(),
            'user_id' => $shelf->getUserId(),
            'created_at' => $shelf->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $shelf->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}