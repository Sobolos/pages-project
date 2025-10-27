<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Quote;

class QuoteMapper extends BaseMapper
{
    public function toEntity(array $row): Quote
    {
        if ($this->getFromMap(Quote::class, $row['id'])) {
            return $this->getFromMap(Quote::class, $row['id']);
        }

        $entity = new Quote(
            id: (int)$row['id'],
            bookId: (int)$row['book_id'],
            userId: (int)$row['user_id'],
            content: $row['content'],
            pageNumber: (int)$row['page_number'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Quote $quote): array
    {
        return [
            'id' => $quote->getId(),
            'book_id' => $quote->getBookId(),
            'user_id' => $quote->getUserId(),
            'content' => $quote->getContent(),
            'page_number' => $quote->getPageNumber(),
            'created_at' => $quote->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $quote->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}