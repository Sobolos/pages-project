<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Rating;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;

class BookMapper extends BaseMapper
{
    public function toEntity(array $row): Book
    {
        if ($this->getFromMap(Book::class, $row['id'])) {
            return $this->getFromMap(Book::class, $row['id']);
        }

        $entity = new Book(
            id: (int)$row['id'],
            title: $row['title'],
            statusId: (int)$row['status_id'],
            rating: new Rating((float)$row['rating']),
            shelfId: (int)$row['shelf_id'],
            userId: (int)$row['user_id'],
            coverUrl: $row['cover_url'] ?? null,
            epubUrl: $row['epub_url'] ?? null,
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at']),
            physicalPagesCount: (int)$row['physical_pages'],
            currentPage: (int)$row['current_page'],
            authorRepository: new PdoAuthorRepository()
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'status_id' => $book->getStatusId(),
            'rating' => $book->getRating()->getValue(),
            'shelf_id' => $book->getShelfId(),
            'user_id' => $book->getUserId(),
            'cover_url' => $book->getCoverUrl(),
            'epub_url' => $book->getEpubUrl(),
            'created_at' => $book->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s'),
            'physical_pages' => $book->getPhysicalPages(),
            'current_page' => $book->getCurrentPage(),
        ];
    }
}