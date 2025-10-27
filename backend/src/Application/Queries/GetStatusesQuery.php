<?php
namespace App\Application\Queries;

use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;

class GetStatusesQuery
{
    private PdoStatusRepository $statusRepository;

    public function __construct()
    {
        $this->statusRepository = new PdoStatusRepository();
    }

    public function execute(array $filters): array
    {
        return $this->statusRepository->findAllWithFilter($filters);
    }
}