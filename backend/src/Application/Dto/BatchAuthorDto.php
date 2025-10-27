<?php

namespace App\Application\Dto;

readonly class BatchAuthorDto
{
    /**
     * @var AuthorDto[]
     */
    public array $authors;
    public int $userId;

    public function __construct (array $authors, int $userId)
    {
        $this->authors = $authors;
        $this->userId = $userId;
    }
}