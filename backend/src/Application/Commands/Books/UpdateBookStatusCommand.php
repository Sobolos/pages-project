<?php
namespace App\Application\Commands\Books;

use App\Application\Dto\UpdateBookDto;
use App\Application\Dto\UpdateBookStatusDto;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\PageProgress;
use App\Domain\ValueObjects\Rating;
use App\Infrastructure\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\BookRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\StatusRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Repositories\Pdo\PdoStatusRepository;
use App\Infrastructure\Services\BookService;
use App\Infrastructure\Services\Validator;

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