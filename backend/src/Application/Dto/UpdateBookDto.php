<?php

namespace App\Application\Dto;

readonly class UpdateBookDto
{
    public int $id;
    public string $title;
    public float $rating;
    public int $shelfId;
    public int $statusId;
    public int $userId;
    public string $coverUrl;
    public string $epubUrl;
    public array $authorsIds;
    public int $physicalPageCount;
    public int $currentPage;
    public ?\DateTimeImmutable $createdAt;

    /**
     * @param int $id
     * @param string $title
     * @param float $rating
     * @param int $shelfId
     * @param int $statusId
     * @param int $userId
     * @param string $coverUrl
     * @param string $epubUrl
     * @param array $authorsIds
     * @param int $physicalPageCount
     * @param int $currentPage
     * @param \DateTimeImmutable|null $createdAt
     */
    public function __construct(
        int $id,
        string $title,
        float $rating,
        int $shelfId,
        int $statusId,
        int $userId,
        string $coverUrl,
        string $epubUrl,
        array $authorsIds,
        int $physicalPageCount,
        int $currentPage,
        ?\DateTimeImmutable $createdAt,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->rating = $rating;
        $this->shelfId = $shelfId;
        $this->statusId = $statusId;
        $this->userId = $userId;
        $this->coverUrl = $coverUrl;
        $this->epubUrl = $epubUrl;
        $this->authorsIds = $authorsIds;
        $this->physicalPageCount = $physicalPageCount;
        $this->currentPage = $currentPage;
        $this->createdAt = $createdAt;
    }
}