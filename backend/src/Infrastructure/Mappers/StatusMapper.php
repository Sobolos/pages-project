<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Status;
use App\Domain\ValueObjects\Color;

class StatusMapper extends BaseMapper
{
    public function toEntity(array $row): Status
    {
        if ($this->getFromMap(Status::class, $row['id'])) {
            return $this->getFromMap(Status::class, $row['id']);
        }

        $entity = new Status(
            id: (int)$row['id'],
            name: $row['name'],
            userId: (int)$row['user_id'],
            color: new Color($row['color']),
            hideFromAgile: (bool)$row['hide_from_agile'],
            position: (int)$row['position'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Status $status): array
    {
        return [
            'id' => $status->getId(),
            'name' => $status->getName(),
            'user_id' => $status->getUserId(),
            'color' => $status->getColor()->getValue(),
            'hide_from_agile' => $status->isHiddenFromAgile() ? 'true' : 'false', // Явное преобразование в строку
            'position' => $status->getPosition(),
            'created_at' => $status->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $status->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}