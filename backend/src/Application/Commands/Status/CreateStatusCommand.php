<?php

namespace App\Application\Commands\Status;

use App\Application\Dto\StatusDto;
use App\Domain\Entities\Status;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;

class CreateStatusCommand
{
    private PdoStatusRepository $statusRepository;

    public function __construct()
    {
        $this->statusRepository = new PdoStatusRepository();
    }

    public function execute(StatusDto $statusDto): Status
    {
        $status = new Status(
            id: 0,
            name: $statusDto->name,
            userId: $statusDto->userId,
            color: $statusDto->color,
            hideFromAgile: $statusDto->hide,
            position: $statusDto->position,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );
        $this->statusRepository->save($status);
        $status->setId($this->statusRepository->pdo->lastInsertId());
        return $status;
    }
}