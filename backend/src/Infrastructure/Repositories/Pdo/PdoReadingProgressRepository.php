<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Author;
use App\Domain\Entities\ReadingProgress;
use App\Infrastructure\Mappers\AuthorMapper;
use App\Infrastructure\Mappers\ReadingProgressMapper;
use App\Infrastructure\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\ReadingProgressRepositoryInterface;

class PdoReadingProgressRepository extends PdoRepository implements ReadingProgressRepositoryInterface
{
    private ReadingProgressMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new ReadingProgressMapper();
    }

    public function findByBookId(int $bookId): ?ReadingProgress
    {
        $stmt = $this->pdo->prepare(
            'SELECT rp.* FROM reading_progress rp WHERE ba.book_id = :book_id'
        );
        $stmt->execute(['book_id' => $bookId]);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM reading_progress WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['name'])) {
            $query .= ' AND name = :name';
            $params['name'] = $filters['name'];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function save(ReadingProgress $entity): void
    {
        $data = $this->mapper->toArray($entity);
        $stmt = $this->pdo->prepare('
            INSERT INTO reading_progress (user_id, book_id, epub_position, physical_page, updated_at)
            VALUES (:user_id, :book_id, :epub_position, :physical_page, :updated_at)
            ON CONFLICT (user_id, book_id) 
            DO UPDATE SET 
                epub_position = EXCLUDED.epub_position,
                physical_page = EXCLUDED.physical_page,
                updated_at = EXCLUDED.updated_at
        ');
        $stmt->execute($data);
    }
}