<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;
use App\Infrastructure\Persistence\PdoConnection;

class CreateSettingsTable extends Migration
{
    public function up(): void
    {
        $pdo = PdoConnection::getInstance()->getPdo();
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS settings (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
                settings JSONB NOT NULL DEFAULT '{}',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP
            );
        SQL);
    }

    public function down(): void
    {
        $pdo = PdoConnection::getInstance()->getPdo();
        $pdo->exec('DROP TABLE IF EXISTS settings;');
    }
}