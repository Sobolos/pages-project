<?php

namespace App\Application\Commands\User;

use App\Application\Dto\LoginDto;
use App\Infrastructure\Services\AuthService;

class LoginUserCommand
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function execute(LoginDto $userDto): array
    {
        return $this->authService->login($userDto);
    }
}