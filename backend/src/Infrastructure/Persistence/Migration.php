<?php
namespace App\Infrastructure\Persistence;

abstract class Migration
{
    protected \PDO $pdo;

    public function setPdo(\PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    abstract public function up(): void;
    abstract public function down(): void;
}