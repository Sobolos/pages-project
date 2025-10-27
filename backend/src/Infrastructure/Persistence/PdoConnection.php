<?php
namespace App\Infrastructure\Persistence;

class PdoConnection
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../../../config/db.php';
        $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']};port=5432";
        $this->pdo = new \PDO($dsn, $config['user'], $config['pass'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}