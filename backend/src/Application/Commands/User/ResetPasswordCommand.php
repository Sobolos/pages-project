<?php

namespace App\Application\Commands\User;

use App\Domain\Entities\User;
use App\Domain\Interfaces\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoUserRepository;

class ResetPasswordCommand
{
    private UserRepositoryInterface $userRepository;

    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
    }

    public function execute(string $token, string $newPassword): array
    {
        $user = $this->userRepository->findByResetToken($token);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Неверный или устаревший токен'];
        }

        if (!$user->isResetTokenValid()) {
            return ['status' => 'error', 'message' => 'Токен сброса пароля истек'];
        }

        // Обновление пароля
        $user->updatePassword(password_hash($newPassword, PASSWORD_BCRYPT));
        $this->userRepository->save($user);

        return ['status' => 'success', 'message' => 'Пароль успешно изменен'];
    }
}
