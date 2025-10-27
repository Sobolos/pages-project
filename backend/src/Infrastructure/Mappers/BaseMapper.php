<?php

namespace App\Infrastructure\Mappers;

use App\Infrastructure\Persistence\PdoConnection;
use App\Infrastructure\Persistence\IdentityMap;

abstract class BaseMapper
{
    protected \PDO $pdo;
    protected IdentityMap $identityMap;

    public function __construct()
    {
        $this->pdo = PdoConnection::getInstance()->getPdo();
        $this->identityMap = new IdentityMap();
    }

    public function addToMap(object $entity, int $id): void
    {
        $this->identityMap->set($entity, $id);
    }

    public function getFromMap(string $class, int $id): ?object
    {
        return $this->identityMap->get($class, $id);
    }
}