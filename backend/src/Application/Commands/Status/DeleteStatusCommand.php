<?php

namespace App\Application\Commands\Status;

use App\Domain\Interfaces\Repositories\StatusRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;


class DeleteStatusCommand
{
    private StatusRepositoryInterface $statusRepository;

    public function __construct()
    {
        $this->statusRepository = new PdoStatusRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $status = $this->statusRepository->findById($id);
        if (!$status || $status->getUserId() !== $userId) {
            throw new \RuntimeException('Status not found or access denied');
        }
        $this->statusRepository->delete($id);
    }
}