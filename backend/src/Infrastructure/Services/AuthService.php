<?php

namespace App\Infrastructure\Services;

use App\Application\Commands\Status\CreateStatusCommand;
use App\Application\Dto\LoginDto;
use App\Application\Dto\StatusDto;
use App\Application\Dto\UpdateStatusDto;
use App\Application\Dto\UserDto;
use App\Domain\Entities\User;
use App\Domain\ValueObjects\Color;
use App\Infrastructure\Repositories\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoUserRepository;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;
use App\Config\jwt;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private CreateStatusCommand $createStatusCommand;
    private HistoryGeneratorServiceInterface $historyGeneratorService;
    private string $jwtSecret;

    public function __construct()
    {
        $this->userRepository = new PdoUserRepository();
        $this->createStatusCommand = new CreateStatusCommand();
        $this->historyGeneratorService = new HistoryGeneratorService();
        $this->jwtSecret = jwt::JWT_SECRET;
    }

    public function register(UserDto $userDto): array
    {
        $user = new User(
            id: 0,
            name: $userDto->name,
            email: $userDto->email,
            password: password_hash($userDto->password, PASSWORD_BCRYPT),
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        $this->userRepository->save($user);

        // Получить ID нового пользователя
        $user = $this->userRepository->findByEmail($userDto->email);
        $userId = $user->getId();

        // Создать дефолтные статусы через команду
        $defaultStatuses = [
            new StatusDto('Планирую', $userId, new Color('#FF0000'), false, 0),
            new StatusDto('Читаю', $userId, new Color('#00FF00'), false, 1),
            new StatusDto('Прочитано', $userId, new Color('#0000FF'), false, 2),
            new StatusDto('Отменено', $userId, new Color('#808080'), false, 3),
        ];

        foreach ($defaultStatuses as $statusData) {
            $this->createStatusCommand->execute($statusData);
        }

        $this->historyGeneratorService->generateUserRegisteredEvent($user);

        return $this->generateTokens($user);
    }

    public function login(LoginDto $loginDto): array
    {
        $user = $this->userRepository->findByName($loginDto->name);
        if (!$user || !password_verify($loginDto->password, $user->getPassword())) {
            throw new \RuntimeException('Invalid credentials');
        }
        return $this->generateTokens($user);
    }

    public function validateToken(string $token): ?int
    {
        try {
            $decoded = $this->decodeJwt($token);
            return (int)$decoded['sub'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateTokens(User $user): array
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + 3600, // 1 час
        ];
        $accessToken = $this->encodeJwt($payload);

        $refreshPayload = [
            'sub' => $user->getId(),
            'iat' => time(),
            'exp' => time() + 7 * 24 * 3600, // 7 дней
        ];
        $refreshToken = $this->encodeJwt($refreshPayload);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user_id' => $user->getId(),
        ];
    }

    private function encodeJwt(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true);
        $signature = base64_encode($signature);
        return "$header.$payload.$signature";
    }

    private function decodeJwt(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid JWT format');
        }
        [$header, $payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true);
        $expectedSignature = base64_encode($expectedSignature);
        if ($signature !== $expectedSignature) {
            throw new \RuntimeException('Invalid JWT signature');
        }
        return json_decode(base64_decode($payload), true);
    }
}