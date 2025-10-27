<?php
namespace App\Application\Commands\Books;

use App\Application\Dto\UpdateBookStatusDto;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\PageProgress;
use App\Infrastructure\Services\BookService;

class UpdateBookStatusCommand
{
    private BookService $bookService;

    public function __construct()
    {
        $this->bookService = new BookService();
    }

    public function execute(UpdateBookStatusDto $updateBookStatusDto, int $userId): Book
    {
        return $this->bookService->changeStatus($updateBookStatusDto, $userId);
    }
}