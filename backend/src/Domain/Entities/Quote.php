<?php

namespace App\Domain\Entities;

class Quote
{
    private int $id;
    private int $bookId;
    private int $userId;
    private string $content;
    private ?string $author;
    private int $pageNumber;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        int $bookId,
        int $userId,
        string $content,
        ?string $author,
        int $pageNumber,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->userId = $userId;
        $this->content = $content;
        $this->author = $author;
        $this->pageNumber = $pageNumber;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBookId(): int
    {
        return $this->bookId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateContent(string $content, int $pageNumber, ?string $author): void
    {
        $this->content = $content;
        $this->pageNumber = $pageNumber;
        $this->author = $author;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setId(int $lastInsertId): self
    {
        $this->id = $lastInsertId;
        return $this;
    }
}