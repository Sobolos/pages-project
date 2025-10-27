<?php

namespace App\Domain\Interfaces\Repositories;

use App\Domain\Entities\HistoryEntry;

interface HistoryRepositoryInterface
{
    public function findById(int $id): ?object;
    public function findAllWithFilter(array $filters): array;
    public function save(HistoryEntry $entity): void;
}