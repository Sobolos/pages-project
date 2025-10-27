<?php
namespace App\Domain\Entities;

class HistoryEntry
{
    private int $id;
    private int $userId;
    private string $eventType;
    private ?int $bookId;
    private ?int $quoteId;
    private ?int $noteId;
    private string $message;
    private \DateTimeImmutable $createdAt;

    /**
     * @param int $id
     * @param int $userId
     * @param string $eventType
     * @param int|null $bookId
     * @param int|null $quoteId
     * @param int|null $noteId
     * @param string $message
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        int $id,
        int $userId,
        string $eventType,
        ?int $bookId,
        ?int $quoteId,
        ?int $noteId,
        string $message,
        \DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->eventType = $eventType;
        $this->bookId = $bookId;
        $this->quoteId = $quoteId;
        $this->noteId = $noteId;
        $this->message = $message;
        $this->createdAt = $createdAt;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getBookId(): ?int
    {
        return $this->bookId;
    }

    public function getQuoteId(): ?int
    {
        return $this->quoteId;
    }

    public function getNoteId(): ?int
    {
        return $this->noteId;
    }
}