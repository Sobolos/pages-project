<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\HistoryEntry;

class HistoryMapper
{
    private array $map = [];

    public function toEntity(array $data): HistoryEntry
    {
        return new HistoryEntry(
            id: (int)($data['id'] ?? 0),
            userId: (int)($data['user_id'] ?? 0),
            eventType: $data['event_type'] ?? '',
            bookId: (int)$data['book_id'] ?? '',
            quoteId: (int)$data['quote_id'] ?? '',
            noteId: (int)$data['note_id'] ?? '',
            message: $data['message'] ?? '',
            createdAt: new \DateTimeImmutable($data['created_at'] ?? 'now')
        );
    }

    public function toArray(HistoryEntry $entity): array
    {
        return [
            'id' => $entity->getId(),
            'user_id' => $entity->getUserId(),
            'event_type' => $entity->getEventType(),
            'book_id' => $entity->getBookId(),
            'quote_id' => $entity->getQuoteId(),
            'note_id' => $entity->getNoteId(),
            'message' => $entity->getMessage(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getFromMap(string $class, int $id): ?object
    {
        return $this->map[$class][$id] ?? null;
    }

    public function addToMap(object $entity, int $id): void
    {
        $this->map[$entity::class][$id] = $entity;
    }
}