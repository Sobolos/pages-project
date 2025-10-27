<?php

namespace App\Infrastructure\Services;

use App\Application\Dto\UpdateBookStatusDto;
use App\Domain\Entities\Book;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use App\Infrastructure\Repositories\Interfaces\BookRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\StatusRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;

class BookService
{
    private BookRepositoryInterface $bookRepository;
    private StorageServiceInterface $coverStorage;
    private StorageServiceInterface $epubStorage;
    private StatusRepositoryInterface $statusRepository;
    private HistoryGeneratorServiceInterface $historyService;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
        $this->coverStorage = new LocalStorageCoverService();
        $this->epubStorage = new LocalEpubStorageService();
        $this->statusRepository = new PdoStatusRepository();
        $this->historyService = new HistoryGeneratorService();
    }

    public function checkBookIsMine(int $bookId, int $userId): bool
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book && $book->getUserId() === $userId) {
            return true;
        }

        return false;
    }

    public function removeBookCover(int $bookId): void
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book->getCoverUrl()) {
            $this->coverStorage->delete($book->getCoverUrl());
        }
    }

    public function setBookCover(int $bookId, string $coverUrl): void
    {
        $book = $this->bookRepository->findById($bookId);
        $book->setCoverUrl($coverUrl);
        $this->bookRepository->save($book);
    }

    public function removeBookEpub(int $bookId): void
    {
        $book = $this->bookRepository->findById($bookId);
        if ($book->getEpubUrl()) {
            $this->epubStorage->delete($book->getCoverUrl());
        }
    }

    public function setBookEpub(int $bookId, string $coverUrl): void
    {
        $book = $this->bookRepository->findById($bookId);
        $book->setEpubUrl($coverUrl);
        $this->bookRepository->save($book);
    }

    public function changeStatus(UpdateBookStatusDto $updateBookStatusDto, int $userId): Book
    {
        $book = $this->bookRepository->findById($updateBookStatusDto->bookId);
        if (!$book || $book->getUserId() !== $userId) {
            throw new \InvalidArgumentException('Book not found or access denied');
        }

        // Получаем старый статус для истории
        $oldStatus = $this->statusRepository->getStatusNameById($book->getStatusId(), $userId);
        $newStatus = $this->statusRepository->getStatusNameById($updateBookStatusDto->status, $userId);

        $book->setStatusId($updateBookStatusDto->status);

        $book = $this->bookRepository->findById($updateBookStatusDto->bookId);
        $book->setStatusId($newStatus->getId());
        $this->bookRepository->save($book);

        // Генерируем событие в истории
        $this->historyService->generateBookStatusChangedEvent(
            bookId: $updateBookStatusDto->bookId,
            userId: $userId,
            bookTitle: $book->getTitle(),
            oldStatus: $oldStatus->getName(),
            newStatus: $newStatus->getName(),
        );

        return $book;
    }
}