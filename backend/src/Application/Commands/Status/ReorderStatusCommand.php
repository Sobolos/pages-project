<?php

namespace App\Application\Commands\Status;

use App\Application\Dto\ReorderStatusesDto;
use App\Domain\Entities\Status;
use App\Infrastructure\Repositories\Interfaces\StatusRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;


class ReorderStatusCommand
{
    private StatusRepositoryInterface $statusRepository;

    public function __construct()
    {
        $this->statusRepository = new PdoStatusRepository();
    }

    public function execute(ReorderStatusesDto $dto, int $userId): void
    {
        $updatedStatuses = [];

        foreach ($dto->items as $item) {
            $status = $this->statusRepository->findById($item['id']);
            if (!$status) {
                throw new \InvalidArgumentException('Status not found: ' . $item['id']);
            }

            // Проверка: статус принадлежит пользователю или системный
            if ($status->getUserId() !== $userId) {
                throw new \InvalidArgumentException('Access denied to status: ' . $item['id']);
            }

            // Создаём обновлённую сущность
            $updatedStatuses[] = new Status(
                id: $status->getId(),
                name: $status->getName(),
                userId: $status->getUserId(),
                color: $status->getColor(),
                hideFromAgile: $status->isHiddenFromAgile(),
                position: $item['position'],
                createdAt: $status->getCreatedAt(),
                updatedAt: new \DateTimeImmutable()
            );

            // Сохраняем все изменения атомарно
            $this->statusRepository->batchUpdate($updatedStatuses);
        }
    }
}