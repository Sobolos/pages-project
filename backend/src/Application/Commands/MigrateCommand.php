<?php
namespace App\Application\Commands;

use App\Infrastructure\Persistence\MigrationRunner;

class MigrateCommand
{
    private MigrationRunner $migrationRunner;

    public function __construct()
    {
        $this->migrationRunner = new MigrationRunner();
    }

    public function execute(string $direction = 'up'): void
    {
        if ($direction === 'up') {
            $this->migrationRunner->run();
            echo "Миграции успешно выполнены.\n";
        } elseif ($direction === 'down') {
            $this->migrationRunner->runDown();
            echo "Миграции успешно откачены.\n";
        } else {
            throw new \InvalidArgumentException("Направление миграции должно быть 'up' или 'down'");
        }
    }
}