<?php

namespace App\Application\Commands\Author;

use App\Application\Dto\AuthorDto;
use App\Domain\Entities\Author;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Services\Validator;

class CreateAuthorCommand
{
    private PdoAuthorRepository $authorRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->authorRepository = new PdoAuthorRepository();
        $this->validator = new Validator();
    }

    public function execute(AuthorDto $authorDto): Author
    {
        $data = [
            'name' => $authorDto->name,
            'user_id' => $authorDto->userId,
        ];

        $errors = $this->validator->validate($data, [
            'name' => ['required', 'string'],
            'user_id' => ['required', 'positive_int'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $author = new Author(
            id: 0,
            name: $authorDto->name,
            userId: $authorDto->userId,
            createdAt: new \DateTimeImmutable(),
        );

        $this->authorRepository->save($author);
        $author->setId($this->authorRepository->pdo->lastInsertId());

        return $author;
    }
}