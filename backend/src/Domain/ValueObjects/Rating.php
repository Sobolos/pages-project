<?php
namespace App\Domain\ValueObjects;

class Rating
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0.0 || $value > 5.0 || fmod($value * 2, 1) !== 0.0) {
            throw new \InvalidArgumentException('Rating must be between 1.0 and 5.0 with 0.5 step');
        }
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function equals(Rating $other): bool
    {
        return $this->value === $other->value;
    }
}