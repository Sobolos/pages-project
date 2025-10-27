<?php
namespace App\Domain\Entities;

readonly class ReadingProgress
{
    public int $userId;
    public int $bookId;
    public float $epubPosition;
    public ?int $physicalPage;
    public \DateTimeImmutable $updatedAt;

    /**
     * @param int $userId
     * @param int $bookId
     * @param float $epubPosition
     * @param int|null $physicalPage
     * @param \DateTimeImmutable $updatedAt
     */
    public function __construct(
        int $userId,
        int $bookId,
        float $epubPosition,
        \DateTimeImmutable $updatedAt,
        ?int $physicalPage = null
    ) {
        $this->userId = $userId;
        $this->bookId = $bookId;
        $this->epubPosition = $epubPosition;
        $this->physicalPage = $physicalPage;
        $this->updatedAt = $updatedAt;
    }

    public function getId()
    {
        return $this->userId;
    }
}