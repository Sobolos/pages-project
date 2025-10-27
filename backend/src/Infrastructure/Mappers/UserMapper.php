<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\User;

class UserMapper extends BaseMapper
{
    public function toEntity(array $row): User
    {
        if ($this->getFromMap(User::class, $row['id'])) {
            return $this->getFromMap(User::class, $row['id']);
        }

        $entity = new User(
            id: (int)$row['id'],
            name: $row['name'],
            email: $row['email'],
            password: $row['password'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at'])
        );
        $this->addToMap($entity, $entity->getId());
        return $entity;
    }

    public function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}