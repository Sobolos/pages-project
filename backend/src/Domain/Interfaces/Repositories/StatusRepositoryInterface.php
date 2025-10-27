<?php

namespace App\Domain\Interfaces\Repositories;

use App\Domain\Entities\Status;

interface StatusRepositoryInterface
{
    public function findById(int $id): ?Status;
    public function findAllWithFilter(array $filters): array;
    public function save(Status $entity): void;
    public function delete(int $id): void;
    public function batchUpdate(array $statuses): void;
}