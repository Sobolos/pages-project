<?php

namespace App\Domain\Interfaces\Repositories;

use App\Domain\Entities\Shelf;

interface ShelfRepositoryInterface
{
    public function findById(int $id): ?Shelf;
    public function findAllWithFilter(array $filters): array;
    public function save(Shelf $entity): void;
    public function delete(int $id): void;
}