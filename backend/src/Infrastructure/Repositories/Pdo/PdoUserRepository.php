<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\User;
use App\Infrastructure\Mappers\UserMapper;
use App\Infrastructure\Repositories\Interfaces\UserRepositoryInterface;

class PdoUserRepository extends PdoRepository implements UserRepositoryInterface
{
    private UserMapper $mapper;
    public function __construct()
    {
        parent::__construct();
        $this->mapper = new UserMapper();
    }

    public function findById(int $id): ?User
    {
        if ($this->mapper->getFromMap(User::class, $id)) {
            return $this->mapper->getFromMap(User::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        if (isset($filters['email'])) {
            $query .= ' AND email = :email';
            $params['email'] = $filters['email'];
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map([$this->mapper, 'toEntity'], $rows);
    }

    public function save(User $entity): void
    {
        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            unset($data['created_at']);
            $stmt = $this->pdo->prepare(
                'UPDATE users SET name = :name, email = :email, password = :password, ' .
                'updated_at = :updated_at WHERE id = :id'
            );
        } else {
            unset($data['id']);

            $stmt = $this->pdo->prepare(
                'INSERT INTO users (name, email, password, created_at, updated_at) ' .
                'VALUES (:name, :email, :password, :created_at, :updated_at)'
            );
        }
        $stmt->execute($data);
        if (!$entity->getId()) {
            $data['id'] = (int)$this->pdo->lastInsertId();
            $this->mapper->addToMap($this->mapper->toEntity($data), $data['id']);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function findByName(string $name): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        return $row ? $this->mapper->toEntity($row) : null;
    }
}