<?php
namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Settings;
use App\Domain\ValueObjects\JsonSettings;

class SettingsMapper
{
    private array $map = [];

    public function toEntity(array $data): Settings
    {
        return new Settings(
            id: (int)($data['id'] ?? 0),
            userId: (int)($data['user_id'] ?? 0),
            jsonSettings: JsonSettings::fromArray(json_decode($data['settings'] ?? '{}', true)),
            createdAt: new \DateTimeImmutable($data['created_at'] ?? 'now'),
            updatedAt: new \DateTimeImmutable($data['updated_at'] ?? 'now')
        );
    }

    public function toArray(Settings $entity): array
    {
        return [
            'id' => $entity->getId(),
            'user_id' => $entity->getUserId(),
            'settings' => json_encode($entity->getJsonSettings()->toArray()),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getFromMap(string $class, int $id): ?object
    {
        return $this->map[$class][$id] ?? null;
    }

    public function addToMap(object $entity, int $id): void
    {
        $this->map[$entity::class][$id] = $entity;
    }
}