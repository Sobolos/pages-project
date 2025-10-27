<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class CreateStatusesTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS statuses (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                color VARCHAR(7) NOT NULL,
                hide_from_agile BOOLEAN NOT NULL DEFAULT FALSE,
                position INTEGER NOT NULL,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP
            );
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS statuses;");
    }
}