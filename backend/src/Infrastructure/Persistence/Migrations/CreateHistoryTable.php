<?php
namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;
use App\Infrastructure\Persistence\PdoConnection;

class CreateHistoryTable extends Migration
{
    public function up(): void
    {
        $pdo = PdoConnection::getInstance()->getPdo();
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS history (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                book_id INTEGER REFERENCES books(id) ON DELETE CASCADE,
                note_id INTEGER REFERENCES notes(id) ON DELETE CASCADE,
                quote_id INTEGER REFERENCES quotes(id) ON DELETE CASCADE,
                event_type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        SQL);
    }

    public function down(): void
    {
        $pdo = PdoConnection::getInstance()->getPdo();
        $pdo->exec('DROP TABLE IF EXISTS history;');
    }
}