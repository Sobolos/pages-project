<?php

namespace App\Application\Commands\Books;

use App\Infrastructure\Services\BookService;

class RemoveBookCoverCommand
{
    private BookService $bookService;

    public function __construct()
    {
        $this->bookService = new BookService();
    }

    public function execute(int $id, int $userId): void
    {
        if (!$this->bookService->checkBookIsMine($id, $userId)) {
            throw new \RuntimeException('Book not found or access denied');
        }

        // Удаляем старую обложку
        $this->bookService->removeBookCover($id);
    }
}