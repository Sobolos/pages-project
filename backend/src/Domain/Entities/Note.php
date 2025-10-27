<?php
namespace App\Domain\Entities;

class Note
{
    private int $id;
    private int $bookId;
    private int $userId;
    private string $content;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        int $bookId,
        int $userId,
        string $content,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->userId = $userId;
        $this->content = $content;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateContent(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }
}