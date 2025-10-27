<?php

namespace App\Infrastructure\Repositories\Interfaces;

use App\Domain\Entities\Note;

interface NoteRepositoryInterface
{
    public function findById(int $id): ?Note;
    public function findAllWithFilter(array $filters): array;
    public function save(Note $entity): void;
    public function delete(int $id): void;
}