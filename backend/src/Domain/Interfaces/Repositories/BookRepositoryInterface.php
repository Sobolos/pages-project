<?php

namespace App\Domain\Interfaces\Repositories;

use App\Domain\Entities\Book;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;
    public function findAllWithFilter(array $filters): array;
    public function save(Book $entity, array $authorIds = []): Book;
    public function delete(int $id): void;
    public function getTitleById(int $id): ?string;
}