<?php

namespace App\Infrastructure\Repositories\Interfaces;

use App\Domain\Entities\DiaryEntry;

interface DiaryRepositoryInterface
{
    public function findById(int $id): ?DiaryEntry;
    public function findAllWithFilter(array $filters): array;
    public function save(DiaryEntry $entity): void;
    public function delete(int $id): void;
}