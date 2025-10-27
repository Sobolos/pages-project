<?php
namespace App\Application\Commands\Quote;

use App\Infrastructure\Repositories\Interfaces\QuoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoQuoteRepository;

class DeleteQuoteCommand
{
    private QuoteRepositoryInterface $quoteRepository;

    public function __construct()
    {
        $this->quoteRepository = new PdoQuoteRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $quote = $this->quoteRepository->findById($id);
        if (!$quote || $quote->getUserId() !== $userId) {
            throw new \RuntimeException('Quote not found or access denied');
        }
        $this->quoteRepository->delete($id);
    }
}