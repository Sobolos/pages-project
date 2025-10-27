<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Author;

class AuthorMapper extends BaseMapper
{
    public function toEntity(array $row): Author
    {
        if ($this->getFromMap(Author::class, $row['id'])) {
            return $this->getFromMap(Author::class, $row['id']);
        }

        $entity = new Author(
            id: (int)$row['id'],
            name: $row['name'],
            userId: (int)$row['user_id'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: $row['updated_at'] ? new \DateTimeImmutable($row['updated_at']) : null
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Author $author): array
    {
        return [
            'id' => $author->getId(),
            'name' => $author->getName(),
            'user_id' => $author->getUserId(),
            'created_at' => $author->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $author->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}