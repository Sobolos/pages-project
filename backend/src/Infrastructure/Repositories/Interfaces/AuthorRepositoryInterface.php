<?php

namespace App\Infrastructure\Repositories\Interfaces;

use App\Domain\Entities\Author;

interface AuthorRepositoryInterface
{
    public function findById(int $id): ?Author;
    public function findAllWithFilter(array $filters): array;
    public function save(Author $entity): void;
    public function delete(int $id): void;
}