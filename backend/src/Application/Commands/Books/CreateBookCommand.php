<?php

namespace App\Application\Commands\Books;

use App\Application\Dto\CreateBookDto;
use App\Domain\Entities\Book;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;
use App\Domain\ValueObjects\Rating;
use App\Infrastructure\Repositories\Interfaces\BookRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Services\HistoryGeneratorService;
use App\Infrastructure\Services\Validator;

class CreateBookCommand
{
    private BookRepositoryInterface $bookRepository;
    private Validator $validator;
    private HistoryGeneratorServiceInterface $historyService;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
        $this->validator = new Validator();
        $this->historyService = new HistoryGeneratorService();
    }

    public function execute(int $userId, CreateBookDto $bookDto): Book
    {
        $data = [
            'user_id' => $bookDto->userId,
            'title' => $bookDto->title,
            'status_id' => $bookDto->statusId,
            'shelf_id' => $bookDto->shelfId,
            'selected_authors' => $bookDto->authorsIds,
            'physical_pages' => $bookDto->physicalPageCount,
        ];

        $errors = $this->validator->validate($data, [
            'user_id' => ['required', 'positive_int'],
            'status_id' => ['required', 'positive_int'],
            'rating' => ['rating'],
            'shelf_id' => ['required', 'positive_int'],
            'selected_authors' => ['array'],
            'physical_pages' => ['positive_int'],
        ]);

        $book = new Book(
            id: 0,
            title: $bookDto->title,
            statusId: $bookDto->statusId,
            rating: new Rating(0.0),
            shelfId: $bookDto->shelfId,
            userId: $userId,
            coverUrl: null,
            epubUrl: null,
            createdAt: new \DateTimeImmutable(),
            physicalPagesCount: $bookDto->physicalPageCount,
            authorRepository: new PdoAuthorRepository()
        );

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $this->bookRepository->save($book, $bookDto->authorsIds);
        $book->setId($this->bookRepository->pdo->lastInsertId());

        $this->historyService->generateBookAddedEvent($book);

        return $book;
    }
}