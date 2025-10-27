<?php

namespace App\Application\Commands\User;

use App\Application\Dto\LoginDto;
use App\Infrastructure\Services\AuthService;

class LoginUserCommand
{
    private authService $authService;

    public function __construct()
    {
        $this->authService = new authService();
    }

    public function execute(LoginDto $userDto): array
    {
        return $this->authService->login($userDto);
    }
}