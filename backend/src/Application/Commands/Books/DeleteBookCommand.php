<?php
namespace App\Application\Commands\Books;

use App\Domain\Interfaces\Repositories\BookRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;

class DeleteBookCommand
{
    private BookRepositoryInterface $bookRepository;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $book = $this->bookRepository->findById($id);
        if (!$book || $book->getUserId() !== $userId) {
            throw new \RuntimeException('Book not found or access denied');
        }

        $this->bookRepository->delete($id);
    }
}