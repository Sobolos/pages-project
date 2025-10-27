<?php

namespace App\Infrastructure\Repositories\Pdo;
use App\Infrastructure\Persistence\PdoConnection;

class PdoRepository
{
    public \PDO $pdo;

    public function __construct()
    {
        $this->pdo = PdoConnection::getInstance()->getPdo();
    }
}