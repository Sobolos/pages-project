<?php
namespace App\Application\Commands\Quote;

use App\Application\Dto\QuoteDto;
use App\Domain\Entities\Quote;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;
use App\Infrastructure\Repositories\Interfaces\BookRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\QuoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Repositories\Pdo\PdoQuoteRepository;
use App\Infrastructure\Services\HistoryGeneratorService;
use App\Infrastructure\Services\Validator;

class CreateQuoteCommand
{
    private QuoteRepositoryInterface $quoteRepository;
    private BookRepositoryInterface $bookRepository;
    private Validator $validator;
    private HistoryGeneratorServiceInterface $historyService;

    public function __construct()
    {
        $this->quoteRepository = new PdoQuoteRepository();
        $this->bookRepository = new PdoBookRepository();
        $this->validator = new Validator();
        $this->historyService = new HistoryGeneratorService();
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
            'book_id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'content' => ['required', 'string'],
            'page_number' => ['required', 'positive_int'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $quote = new Quote(
            id: 0,
            bookId: $quoteDto->bookId,
            userId: $quoteDto->userId,
            content: $quoteDto->content,
            pageNumber: $quoteDto->pageNumber,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable()
        );

        $this->quoteRepository->save($quote);


        $bookTitle = $this->bookRepository->getTitleById($quoteDto->bookId);
        $this->historyService->generateQuoteAddedEvent($quote, $bookTitle);

        return $quote;
    }
}