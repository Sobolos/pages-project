<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Color;

class Status
{
    private int $id;
    private string $name;
    private int $userId;
    private Color $color;
    private bool $hideFromAgile;
    private int $position;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        string $name,
        int $userId,
        Color $color,
        bool $hideFromAgile,
        int $position,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->userId = $userId;
        $this->color = $color;
        $this->hideFromAgile = $hideFromAgile;
        $this->position = $position;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function isHiddenFromAgile(): bool
    {
        return $this->hideFromAgile;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(string $name, Color $color, bool $hideFromAgile): void
    {
        $this->name = $name;
        $this->color = $color;
        $this->hideFromAgile = $hideFromAgile;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}