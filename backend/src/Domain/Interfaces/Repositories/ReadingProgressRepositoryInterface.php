<?php

namespace App\Domain\Interfaces\Repositories;

use App\Domain\Entities\ReadingProgress;

interface ReadingProgressRepositoryInterface
{
    public function findByBookId(int $bookId): ?ReadingProgress;
    public function findAllWithFilter(array $filters): array;
    public function save(ReadingProgress $entity): void;
}