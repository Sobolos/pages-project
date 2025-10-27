<?php

namespace App\Domain\Interfaces;

use App\Domain\Entities\Book;
use App\Domain\Entities\Note;
use App\Domain\Entities\Quote;
use App\Domain\Entities\User;

interface HistoryGeneratorServiceInterface
{
    public function generateBookAddedEvent(Book $book): void;
    public function generateUserRegisteredEvent(User $user): void;

    public function generateQuoteAddedEvent(Quote $quote, string $bookTitle): void;

    public function generateNoteAddedEvent(Note $note, string $bookTitle): void;

    public function generateBookStatusChangedEvent(int $bookId, int $userId, string $bookTitle, string $oldStatus, string $newStatus): void;

    public function generateBookShelfChangedEvent(int $bookId, int $userId, string $bookTitle, string $oldShelf, string $newShelf): void;
}