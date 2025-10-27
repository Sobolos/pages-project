<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class CreateBookAuthorsTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS book_authors (
                book_id INTEGER REFERENCES books(id) ON DELETE CASCADE,
                author_id INTEGER REFERENCES authors(id) ON DELETE CASCADE,
                PRIMARY KEY (book_id, author_id)
            );
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS book_authors;");
    }
}