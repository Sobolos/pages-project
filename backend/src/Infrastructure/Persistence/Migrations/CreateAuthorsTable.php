<?php
namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class CreateAuthorsTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS authors (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP,
                UNIQUE(user_id, name)
            );
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS authors;");
    }
}