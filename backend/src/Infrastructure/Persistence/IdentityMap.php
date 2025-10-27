<?php

namespace App\Infrastructure\Persistence;

class IdentityMap
{
    private array $map = [];

    public function get(string $class, int $id): ?object
    {
        return $this->map[$class][$id] ?? null;
    }

    public function set(object $entity, int $id): void
    {
        $class = get_class($entity);
        if (!isset($this->map[$class])) {
            $this->map[$class] = [];
        }
        $this->map[$class][$id] = $entity;
    }

    public function has(string $class, int $id): bool
    {
        return isset($this->map[$class][$id]);
    }
}