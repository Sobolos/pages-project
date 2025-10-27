<?php
namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class CreateBooksTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS books (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                status_id INTEGER REFERENCES statuses(id) ON DELETE SET NULL,
                rating FLOAT NOT NULL,
                shelf_id INTEGER REFERENCES shelves(id) ON DELETE SET NULL,
                user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                cover_url VARCHAR(255),
                epub_url VARCHAR(255),
                physical_pages INTEGER NOT NULL DEFAULT 0,
                current_page INTEGER NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP
            );
        ");
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS books;");
    }
}