<?php

namespace App\Application\Commands\Shelf;

use App\Application\Dto\ShelfDto;
use App\Domain\Entities\Shelf;
use App\Infrastructure\Repositories\Interfaces\ShelfRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoShelfRepository;
use App\Infrastructure\Services\Validator;

class CreateShelfCommand
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
            'name' => $shelfDto->name,
            'user_id' => $shelfDto->userId,
        ];

        $errors = $this->validator->validate($data, [
            'name' => ['required', 'string'],
            'user_id' => ['required', 'positive_int'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $shelf = new Shelf(
            id: 0,
            name: $shelfDto->name,
            userId: $shelfDto->userId,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );

        $this->shelfRepository->save($shelf);
        return $shelf;
    }
}