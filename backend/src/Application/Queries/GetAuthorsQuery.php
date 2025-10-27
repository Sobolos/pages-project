<?php
namespace App\Application\Queries;

use App\Domain\Interfaces\Repositories\AuthorRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;

class GetAuthorsQuery
{
    private AuthorRepositoryInterface $authorRepository;

    public function __construct()
    {
        $this->authorRepository = new PdoAuthorRepository();
    }

    public function execute(array $filters): array
    {
        return $this->authorRepository->findAllWithFilter($filters);
    }
}