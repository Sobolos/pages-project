<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class CreateReadingProgressTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS reading_progress (
                user_id INT NOT NULL,
                book_id INT NOT NULL,
                epub_position NUMERIC(5,4) NOT NULL CHECK (epub_position BETWEEN 0 AND 1),
                updated_at TIMESTAMP,
                PRIMARY KEY (user_id, book_id)
            )
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("
            DROP TABLE IF EXISTS reading_progress;
        ");
    }
}