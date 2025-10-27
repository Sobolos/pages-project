<?php

namespace App\Application\Dto;

readonly class LoginDto
{
    public string $name;
    public string $password;

    public function __construct(string $name, string $password)
    {
        $this->name = $name;
        $this->password = $password;
    }
}