<?php

namespace App\Infrastructure\Persistence\Migrations;

use App\Infrastructure\Persistence\Migration;

class AddQuoteAuthor extends Migration
{
    public function up(): void
    {
        $this->pdo->exec("
            ALTER TABLE quotes 
            ADD COLUMN author VARCHAR(255) NULL"
        );
    }

    public function down(): void
    {
        $this->pdo->exec("
            ALTER TABLE quotes
            DROP COLUMN author"
        );
    }
}
