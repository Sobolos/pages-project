<?php

namespace App\Application\Commands\Author;

use App\Application\Dto\AuthorDto;
use App\Domain\Entities\Author;
use App\Domain\Interfaces\Repositories\AuthorRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoAuthorRepository;
use App\Infrastructure\Services\Validator;

class UpdateAuthorCommand
{
    private AuthorRepositoryInterface $authorRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->authorRepository = new PdoAuthorRepository();
        $this->validator = new Validator();
    }

    public function execute(AuthorDto $authorDto): Author
    {
        $data = [
            'id' => $authorDto->id,
            'user_id' => $authorDto->userId,
            'name' => $authorDto->name,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'name' => ['required', 'string'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $author = $this->authorRepository->findById($authorDto->id);
        if (!$author || $author->getUserId() !== $authorDto->userId) {
            throw new \RuntimeException('Author not found or access denied');
        }

        $author->updateName($authorDto->name);
        $this->authorRepository->save($author);
        return $author;
    }
}