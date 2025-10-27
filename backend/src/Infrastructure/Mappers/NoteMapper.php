<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Note;

class NoteMapper extends BaseMapper
{
    public function toEntity(array $row): Note
    {
        if ($this->getFromMap(Note::class, $row['id'])) {
            return $this->getFromMap(Note::class, $row['id']);
        }

        $entity = new Note(
            id: (int)$row['id'],
            bookId: (int)$row['book_id'],
            userId: (int)$row['user_id'],
            content: $row['content'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Note $note): array
    {
        return [
            'id' => $note->getId(),
            'book_id' => $note->getBookId(),
            'user_id' => $note->getUserId(),
            'content' => $note->getContent(),
            'created_at' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $note->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}