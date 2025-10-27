<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\Book;
use App\Domain\Entities\HistoryEntry;
use App\Domain\Entities\Note;
use App\Domain\Entities\Quote;
use App\Domain\Entities\User;
use App\Infrastructure\Repositories\Interfaces\HistoryRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoHistoryRepository;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;

class HistoryGeneratorService implements HistoryGeneratorServiceInterface
{
    private HistoryRepositoryInterface $historyRepository;
    public function __construct()
    {
        $this->historyRepository = new PdoHistoryRepository();
    }

    public function generateUserRegisteredEvent(User $user): void
    {
        $message = "Пользователь {$user->getName()} зарегистрировался {$user->getCreatedAt()->format('d.m.Y')}";
        $this->saveEntry($user->getId(), 'user_registered', $message);
    }

    public function generateBookAddedEvent(Book $book): void
    {
        $message = "{$book->getTitle()}, Добавлена в библиотеку.";
        $this->saveEntry($book->getUserId(), 'book_added', $message, bookId: $book->getId());
    }

    public function generateQuoteAddedEvent(Quote $quote, string $bookTitle): void
    {
        $page = $quote->physicalPage ?? 'неизвестно';
        $source = $quote->source ?? 'не указан';
        $message = "Цитата из книги {$bookTitle}: '{$quote->getContent()}', {$source}. страница {$page}";
        $this->saveEntry($quote->getUserId(), 'quote_added', $message, bookId: $quote->getBookId(), quoteId: $quote->getId());
    }

    public function generateNoteAddedEvent(Note $note, string $bookTitle): void
    {
        $page = $note->physicalPage ?? 'неизвестно';
        $message = "Заметка для книги {$bookTitle}: '{$note->getContent()}'. страница {$page}";
        $this->saveEntry($note->getUserId(), 'note_added', $message, bookId: $note->getBookId(), noteId: $note->getId());
    }

    public function generateBookStatusChangedEvent(int $bookId, int $userId, string $bookTitle, string $oldStatus, string $newStatus): void
    {
        $message = "{$bookTitle} сменила статус: {$oldStatus} -> {$newStatus}";
        $this->saveEntry($userId, 'status_changed', $message, bookId: $bookId);
    }

    public function generateBookShelfChangedEvent(int $bookId, int $userId, string $bookTitle, string $oldShelf, string $newShelf): void
    {
        $message = "{$bookTitle} перемещена на полку: {$oldShelf} -> {$newShelf}";
        $this->saveEntry($userId, 'shelf_changed', $message, bookId: $bookId);
    }

    private function saveEntry(
        int $userId,
        string $eventType,
        string $message,
        ?int $bookId = null,
        ?int $quoteId = null,
        ?int $noteId = null,
    ): void {
        $entry = new HistoryEntry(
            id: 0,
            userId: $userId,
            eventType: $eventType,
            bookId: $bookId,
            quoteId: $quoteId,
            noteId: $noteId,
            message: $message,
            createdAt: new \DateTimeImmutable()
        );

        $this->historyRepository->save($entry);
    }
}