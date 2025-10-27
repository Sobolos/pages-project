<?php

namespace App\Application\Queries;

use App\Domain\Interfaces\Repositories\ShelfRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoShelfRepository;

class GetShelvesQuery
{
    private ShelfRepositoryInterface $shelfRepository;

    public function __construct()
    {
        $this->shelfRepository = new PdoShelfRepository();
    }

    public function execute(array $filters): array
    {
        return $this->shelfRepository->findAllWithFilter($filters);
    }
}