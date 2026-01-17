<?php

namespace App\Application\Commands\User;

use App\Application\Dto\UserDto;
use App\Domain\Exceptions\EmailRegistredException;
use App\Domain\Exceptions\LoginTakenException;
use App\Infrastructure\Services\AuthService;

class RegisterUserCommand
{
    private authService $authService;

    public function __construct()
    {
        $this->authService = new authService();
    }

    /**
     * @throws EmailRegistredException
     * @throws LoginTakenException
     */
    public function execute(UserDto $userDto): array
    {
        return $this->authService->register($userDto);
    }
}