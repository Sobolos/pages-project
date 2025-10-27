<?php

namespace App\Application\Commands\Shelf;

use App\Application\Dto\ShelfDto;
use App\Domain\Entities\Shelf;
use App\Domain\Interfaces\Repositories\ShelfRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoShelfRepository;
use App\Infrastructure\Services\Validator;

class UpdateShelfCommand
{
    private ShelfRepositoryInterface $shelfRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->shelfRepository = new PdoShelfRepository();
        $this->validator = new Validator();
    }

    public function execute(ShelfDto $shelfDto): Shelf
    {
        $data = [
            'id' => $shelfDto->id,
            'user_id' => $shelfDto->userId,
            'name' => $shelfDto->name,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'name' => ['required', 'string'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $shelf = $this->shelfRepository->findById($shelfDto->id);
        if (!$shelf || $shelf->getUserId() !== $shelfDto->userId) {
            throw new \RuntimeException('Shelf not found or access denied');
        }

        $shelf->updateName($shelfDto->name);
        $this->shelfRepository->save($shelf);
        return $shelf;
    }
}