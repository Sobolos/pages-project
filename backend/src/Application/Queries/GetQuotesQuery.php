<?php
namespace App\Application\Queries;

use App\Domain\Interfaces\Repositories\QuoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoQuoteRepository;

class GetQuotesQuery
{
    private QuoteRepositoryInterface $quoteRepository;

    public function __construct()
    {
        $this->quoteRepository = new PdoQuoteRepository();
    }

    public function execute(array $filters): array
    {
        return $this->quoteRepository->findAllWithFilter($filters);
    }
}