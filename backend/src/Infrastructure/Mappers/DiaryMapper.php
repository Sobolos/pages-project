<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\DiaryEntry;

class DiaryMapper extends BaseMapper
{
    public function toEntity(array $row): DiaryEntry
    {
        if ($this->getFromMap(DiaryEntry::class, $row['id'])) {
            return $this->getFromMap(DiaryEntry::class, $row['id']);
        }

        $entity = new DiaryEntry(
            id: (int)$row['id'],
            userId: (int)$row['user_id'],
            readingMood: (int)$row['reading_mood'],
            message: $row['message'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['created_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(DiaryEntry $author): array
    {
        return [
            'id' => $author->getId(),
            'user_id' => $author->getUserId(),
            'reading_mood' => $author->getReadingMood(),
            'message' => $author->getMessage(),
            'created_at' => $author->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $author->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}