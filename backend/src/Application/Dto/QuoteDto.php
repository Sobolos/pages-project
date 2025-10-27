<?php

namespace App\Application\Dto;

readonly class QuoteDto
{
    public ?int $id;
    public ?int $bookId;
    public string $content;
    public int $userId;
    public int $pageNumber;

    public function __construct(
        string $content,
        int $userId,
        int $pageNumber,
        ?int $bookId = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->content = $content;
        $this->userId = $userId;
        $this->pageNumber = $pageNumber;
    }
}