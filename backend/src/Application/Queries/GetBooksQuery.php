<?php

namespace App\Application\Queries;

use App\Domain\Entities\Book;
use App\Domain\Interfaces\Repositories\BookRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;

class GetBooksQuery
{
    private BookRepositoryInterface $bookRepository;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
    }

    /**
     * @param array $filters
     * @return Book[]
     */
    public function execute(array $filters): array
    {
        return $this->bookRepository->findAllWithFilter($filters);
    }
}