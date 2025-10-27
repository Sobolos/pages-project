<?php

namespace App\Application\Commands\Status;

use App\Application\Dto\UpdateStatusDto;
use App\Domain\Entities\Status;
use App\Domain\Interfaces\Repositories\StatusRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;
use App\Infrastructure\Services\Validator;

class UpdateStatusCommand
{
    private StatusRepositoryInterface $statusRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->statusRepository = new PdoStatusRepository();
        $this->validator = new Validator();
    }

    public function execute(UpdateStatusDto $statusDto): Status
    {
        $data = [
            'id' => $statusDto->id,
            'user_id' => $statusDto->userId,
            'name' => $statusDto->name,
            'color' => $statusDto->color->getValue(),
            'hide_from_agile' => $statusDto->hide,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'name' => ['required', 'string'],
            'color' => ['required', 'hex_color'],
            'hide_from_agile' => ['boolean'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $status = $this->statusRepository->findById($statusDto->id);
        if (!$status || $status->getUserId() !== $statusDto->userId) {
            throw new \RuntimeException('Status not found or access denied');
        }

        $status->update($statusDto->name, $statusDto->color, $statusDto->hide);
        $this->statusRepository->save($status);
        return $status;
    }
}