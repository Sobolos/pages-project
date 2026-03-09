<?php

namespace App\Application\Commands\User;

use App\Domain\Interfaces\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoUserRepository;
use App\Infrastructure\Services\Cache\RateLimiter;
use App\Infrastructure\Services\EmailService;

class ForgotPasswordCommand
{
    private UserRepositoryInterface $userRepository;
    private EmailService $emailService;
    private RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
        $this->emailService = new EmailService();
        $this->rateLimiter = new RateLimiter(3, 15);
    }

    public function execute(string $email): array
    {
        // Проверка лимита попыток по email
        $emailKey = 'forgot_password:' . $email;
        if (!$this->rateLimiter->isAllowed($emailKey)) {
            $remainingTime = $this->rateLimiter->getRemainingTime($emailKey);
            $minutes = ceil($remainingTime / 60);
            return [
                'status' => 'error', 
                'message' => "Превышено количество попыток. Повторите через $minutes минут"
            ];
        }

        $user = $this->userRepository->findByEmail($email);
        // Даже если пользователь не найден, все равно увеличиваем счетчик попыток для защиты от перебора email
        $this->rateLimiter->attempt($emailKey);
        
        if (!$user) {
            // Для несуществующих email возвращаем успешный статус, чтобы не раскрывать существование email
            return [
                'status' => 'success', 
                'message' => 'Ссылка для сброса пароля отправлена на ваш email'
            ];
        }

        // Генерация токена сброса пароля
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenExpiry = new \DateTimeImmutable('+1 hour');

        // Сохранение токена в базе данных
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiry($resetTokenExpiry);
        $this->userRepository->save($user);

        // Отправка email с ссылкой для сброса пароля
        $this->emailService->sendPasswordResetEmail($user, $resetToken);

        return ['status' => 'success', 'message' => 'Ссылка для сброса пароля отправлена на ваш email'];
    }
}
