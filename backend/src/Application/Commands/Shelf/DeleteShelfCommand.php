<?php

namespace App\Application\Commands\Shelf;

use App\Infrastructure\Repositories\Interfaces\ShelfRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoShelfRepository;

class DeleteShelfCommand
{
    private ShelfRepositoryInterface $shelfRepository;

    public function __construct()
    {
        $this->shelfRepository = new PdoShelfRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $shelf = $this->shelfRepository->findById($id);
        if (!$shelf || $shelf->getUserId() !== $userId) {
            throw new \RuntimeException('Shelf not found or access denied');
        }
        $this->shelfRepository->delete($id);
    }
}