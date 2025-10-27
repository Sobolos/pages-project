<?php

namespace App\Application\Dto;

readonly class ShelfDto
{
    public ?int $id;
    public string $name;
    public int $userId;

    public function __construct(string $name, int $userId, ?int $id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->userId = $userId;
    }
}