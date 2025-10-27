<?php
namespace App\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Migrations\CreateHistoryTable;
use App\Infrastructure\Persistence\Migrations\CreateReadingProgressTable;
use App\Infrastructure\Persistence\Migrations\CreateSettingsTable;
use App\Infrastructure\Persistence\Migrations\CreateUsersTable;
use App\Infrastructure\Persistence\Migrations\CreateBooksTable;
use App\Infrastructure\Persistence\Migrations\CreateAuthorsTable;
use App\Infrastructure\Persistence\Migrations\CreateBookAuthorsTable;
use App\Infrastructure\Persistence\Migrations\CreateStatusesTable;
use App\Infrastructure\Persistence\Migrations\CreateShelvesTable;
use App\Infrastructure\Persistence\Migrations\CreateNotesQuotesTables;

class MigrationRunner
{
    private \PDO $pdo;
    private array $migrations;

    public function __construct()
    {
        $this->pdo = PdoConnection::getInstance()->getPdo();

        $this->migrations = [
            new CreateUsersTable(),
            new CreateAuthorsTable(),
            new CreateStatusesTable(),
            new CreateShelvesTable(),
            new CreateBooksTable(),
            new CreateBookAuthorsTable(),
            new CreateNotesQuotesTables(),
            new CreateHistoryTable(),
            new CreateSettingsTable(),
            new CreateReadingProgressTable()
        ];
    }

    public function run(): void
    {
        foreach ($this->migrations as $migration) {
            $migration->setPdo($this->pdo);
            $migration->up();
        }
    }

    public function runDown(): void
    {
        foreach ($this->migrations as $migration) {
            $migration->setPdo($this->pdo);
            $migration->down();
        }
    }
}