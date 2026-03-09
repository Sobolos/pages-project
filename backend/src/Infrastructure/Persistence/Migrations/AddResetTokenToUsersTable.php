<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class AddResetTokenToUsersTable extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE users 
            ADD COLUMN reset_token VARCHAR(255) NULL,
            ADD COLUMN reset_token_expiry TIMESTAMP NULL"
        );
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE users
            DROP COLUMN reset_token,
            DROP COLUMN reset_token_expiry"
        );
    }
}
