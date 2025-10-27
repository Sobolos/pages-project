<?php

namespace App\Domain\ValueObjects;

class Color
{
    private string $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            throw new \InvalidArgumentException('Color must be a valid hex code (e.g., #FF0000)');
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Color $other): bool
    {
        return $this->value === $other->value;
    }
}