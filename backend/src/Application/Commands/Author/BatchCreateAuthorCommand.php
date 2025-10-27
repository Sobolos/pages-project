<?php

namespace App\Application\Commands\Author;

use App\Application\Dto\AuthorDto;
use App\Application\Dto\BatchAuthorDto;
use App\Domain\Entities\Author;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Services\Validator;

class BatchCreateAuthorCommand
{
    private PdoAuthorRepository $authorRepository;

    public function __construct()
    {
        $this->authorRepository = new PdoAuthorRepository();
    }

    /**
     * @param BatchAuthorDto $batchAuthorDto
     * @return Author[]
     */
    public function execute(BatchAuthorDto $batchAuthorDto): array
    {
        $existing = $this->authorRepository->findAllWithFilter(['userId' => $batchAuthorDto->userId]);
        $existingNames = array_map(function (Author $author) {
            return $author->getName();
        }, $existing);

        $providedNames = array_map(function (AuthorDto $author) {
            return $author->name;
        }, $batchAuthorDto->authors);

        $toCreate = array_diff($providedNames, $existingNames);
        $newAuthors = [];

        if (!empty($toCreate)) {
            foreach ($toCreate as $name) {
                $newAuthors[] = new Author(
                    id: 0,
                    name: $name,
                    userId: $batchAuthorDto->userId,
                    createdAt: new \DateTimeImmutable(),
                    updatedAt: new \DateTimeImmutable()
                );
            }

            $this->authorRepository->batchCreate($newAuthors, $batchAuthorDto->userId);
        }

        return $this->authorRepository->findByNameBatch($batchAuthorDto->userId, $providedNames);
    }
}