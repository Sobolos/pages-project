<?php

namespace App\Infrastructure\Repositories\Interfaces;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByName(string $name): ?User;
    public function findAllWithFilter(array $filters): array;
    public function save(User $entity): void;
    public function delete(int $id): void;
}