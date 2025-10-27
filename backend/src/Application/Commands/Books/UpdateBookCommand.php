<?php
namespace App\Application\Commands\Books;

use App\Application\Dto\UpdateBookDto;
use App\Domain\Entities\Book;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;
use App\Domain\Interfaces\Repositories\AuthorRepositoryInterface;
use App\Domain\Interfaces\Repositories\BookRepositoryInterface;
use App\Domain\ValueObjects\Rating;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Services\HistoryGeneratorService;
use App\Infrastructure\Services\Validator;

class UpdateBookCommand
{
    private BookRepositoryInterface $bookRepository;
    private AuthorRepositoryInterface $authorRepository;
    private Validator $validator;
    private HistoryGeneratorServiceInterface $historyService;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
        $this->authorRepository = new PdoAuthorRepository();
        $this->validator = new Validator();
        $this->historyService = new HistoryGeneratorService();
    }

    public function execute(UpdateBookDto $updateBookDto, int $userId): Book
    {
        $data = [
            'id' => $updateBookDto->id,
            'user_id' => $updateBookDto->userId,
            'title' => $updateBookDto->title,
            'status_id' => $updateBookDto->statusId,
            'rating' => $updateBookDto->rating,
            'shelf_id' => $updateBookDto->shelfId,
            'author_ids' => $updateBookDto->authorsIds,
            'cover_url' => $updateBookDto->coverUrl,
            'epub_url' => $updateBookDto->epubUrl,
            'physical_pages' => $updateBookDto->physicalPageCount,
            'current_page' => $updateBookDto->currentPage,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'title' => ['required', 'string'],
            'status_id' => ['required', 'positive_int'],
            'rating' => ['rating'],
            'shelf_id' => ['required', 'positive_int'],
            'author_ids' => ['array'],
            'cover_url' => ['string'],
            'epub_url' => ['string'],
            'physical_pages' => ['positive_int'],
            'electronic_pages' => ['positive_int'],
            'current_page' => ['positive_int'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $book = $this->bookRepository->findById($updateBookDto->id);

        if ($book->getUserId() !== $userId) {
            throw new \RuntimeException('Book not found or access denied');
        }

        $oldShelf = $book->getShelfId();
        $newShelf = $updateBookDto->shelfId;

        $book = new Book(
            id: $updateBookDto->id,
            title: $updateBookDto->title,
            statusId: $updateBookDto->statusId,
            rating: new Rating($updateBookDto->rating),
            shelfId: $updateBookDto->shelfId,
            userId: $updateBookDto->userId,
            coverUrl: $updateBookDto->coverUrl,
            epubUrl: $updateBookDto->epubUrl,
            createdAt: $updateBookDto->createdAt,
            physicalPagesCount: $updateBookDto->physicalPageCount,
            currentPage: $updateBookDto->currentPage,
            authorRepository: $this->authorRepository,
            updatedAt: new \DateTimeImmutable()
        );

        $this->bookRepository->save($book, $updateBookDto->authorsIds);

        if ($oldShelf !== $newShelf) {
            $this->historyService->generateBookShelfChangedEvent($book->getId(), $userId, $book->getTitle(), $oldShelf, $newShelf);
        }

        return $book;
    }
}