<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\ReadingProgress;

class ReadingProgressMapper extends BaseMapper
{
    /**
     * Преобразует строку из БД → сущность Domain
     */
    public function toEntity(array $row): ReadingProgress
    {
        return new ReadingProgress(
            userId: (int)$row['user_id'],
            bookId: (int)$row['book_id'],
            epubPosition: (float)$row['epub_position'],
            updatedAt: new \DateTimeImmutable($row['updated_at']),
            physicalPage: isset($row['physical_page']) ? (int)$row['physical_page'] : null
        );
    }

    /**
     * Преобразует сущность → массив для вставки/обновления в БД
     */
    public function toArray(ReadingProgress $progress): array
    {
        return [
            'user_id' => $progress->userId,
            'book_id' => $progress->bookId,
            'epub_position' => $progress->epubPosition,
            'physical_page' => $progress->physicalPage,
            'updated_at' => $progress->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}