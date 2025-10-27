<?php
namespace App\Application\Commands\Quote;

use App\Application\Dto\QuoteDto;
use App\Domain\Entities\Quote;
use App\Infrastructure\Repositories\Interfaces\QuoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoQuoteRepository;
use App\Infrastructure\Services\Validator;

class UpdateQuoteCommand
{
    private QuoteRepositoryInterface $quoteRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->quoteRepository = new PdoQuoteRepository();
        $this->validator = new Validator();
    }
    public function execute(QuoteDto $quoteDto): Quote
    {
        $data = [
            'book_id' => $quoteDto->bookId,
            'user_id' => $quoteDto->userId,
            'content' => $quoteDto->content,
            'page_number' => $quoteDto->pageNumber,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'content' => ['required', 'string'],
            'page_number' => ['required', 'positive_int'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $quote = $this->quoteRepository->findById($quoteDto->id);
        if (!$quote || $quote->getUserId() !== $quoteDto->userId) {
            throw new \RuntimeException('Quote not found or access denied');
        }

        $quote->updateContent($quoteDto->content, $quoteDto->pageNumber);
        $this->quoteRepository->save($quote);
        return $quote;
    }
}