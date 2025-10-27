<?php

namespace App\Application\Commands\Books;

use App\Domain\Interfaces\StorageServiceInterface;
use App\Infrastructure\Services\BookService;
use App\Infrastructure\Services\LocalStorageCoverService;

/**
 * Команда для загрузки обложки при создании книги
 * В отличие от UploadBookCoverCommand, не удаляет старую обложку
 */
class UploadCoverOnCreateCommand
{
    private StorageServiceInterface $storageService;
    private BookService $bookService;

    public function __construct()
    {
        $this->storageService = new LocalStorageCoverService();
        $this->bookService = new BookService();
    }

    public function execute(int $id, int $userId, array $uploadedFile): void
    {
        if (!$this->bookService->checkBookIsMine($id, $userId)) {
            throw new \RuntimeException('Book not found or access denied');
        }

        // Сохраняем новую обложку
        $coverUrl = $this->storageService->saveFromUpload($uploadedFile);

        $this->bookService->setBookCover($id, $coverUrl);
    }
}
