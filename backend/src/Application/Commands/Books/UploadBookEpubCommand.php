<?php

namespace App\Application\Commands\Books;

use App\Domain\Interfaces\StorageServiceInterface;
use App\Infrastructure\Services\BookService;
use App\Infrastructure\Services\LocalEpubStorageService;

class UploadBookEpubCommand
{
    private StorageServiceInterface $storageService;
    private BookService $bookService;

    public function __construct()
    {
        $this->storageService = new LocalEpubStorageService();
        $this->bookService = new BookService();
    }

    public function execute(int $id, int $userId, array $uploadedFile): void
    {
        if (!$this->bookService->checkBookIsMine($id, $userId)) {
            throw new \RuntimeException('Book not found or access denied');
        }

        // Удаляем старую обложку
        $this->bookService->removeBookCover($id);

        // Сохраняем новую
        $epubUrl = $this->storageService->saveFromUpload($uploadedFile);

        $this->bookService->setBookEpub($id, $epubUrl);
    }
}