<?php

namespace App\Application\Dto;

use App\Domain\ValueObjects\Color;

readonly class StatusDto
{
    public ?int $id;
    public string $name;
    public int $userId;
    public Color $color;
    public bool $hide;
    public int $position;

    /**
     * @param int|null $id
     * @param string $name
     * @param int $userId
     * @param Color $color
     * @param bool $hide
     */
    public function __construct(
        string $name,
        int $userId,
        Color $color,
        bool $hide,
        int $position,
        ?int $id = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->userId = $userId;
        $this->color = $color;
        $this->hide = $hide;
        $this->position = $position;
    }
}