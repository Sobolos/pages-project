<?php

namespace App\Infrastructure\Repositories\Interfaces;

use App\Domain\Entities\Quote;

interface QuoteRepositoryInterface
{
    public function findById(int $id): ?Quote;
    public function findAllWithFilter(array $filters): array;
    public function save(Quote $entity): void;
    public function delete(int $id): void;
}