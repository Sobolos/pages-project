<?php

namespace App\Application\Commands\Author;

use App\Infrastructure\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;

class DeleteAuthorCommand
{
    private AuthorRepositoryInterface $authorRepository;

    public function __construct()
    {
        $this->authorRepository = new PdoAuthorRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $author = $this->authorRepository->findById($id);
        if (!$author || $author->getUserId() !== $userId) {
            throw new \RuntimeException('Author not found or access denied');
        }
        $this->authorRepository->delete($id);
    }
}