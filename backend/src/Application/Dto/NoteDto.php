<?php

namespace App\Application\Dto;

readonly class NoteDto
{
    public ?int $id;
    public ?int $bookId;
    public string $content;
    public int $userId;

    public function __construct(
        string $content,
        int $userId,
        ?int $bookId = null,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->content = $content;
        $this->userId = $userId;
    }
}