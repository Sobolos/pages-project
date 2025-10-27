<?php
namespace App\Domain\Entities;

use App\Domain\Interfaces\Repositories\AuthorRepositoryInterface;
use App\Domain\ValueObjects\Rating;

class Book
{
    private int $id;
    private string $title;
    private int $statusId;
    private Rating $rating;
    private int $shelfId;
    private int $userId;
    private ?string $coverUrl;
    private ?string $epubUrl;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;
    private int $physicalPageCount;
    private ?AuthorRepositoryInterface $authorRepository;
    private ?array $authors = null;
    private int $currentPage;

    public function __construct(
        int $id,
        string $title,
        int $statusId,
        Rating $rating,
        int $shelfId,
        int $userId,
        ?string $coverUrl,
        ?string $epubUrl,
        \DateTimeImmutable $createdAt,
        int $physicalPagesCount,
        int $currentPage = 0,
        ?AuthorRepositoryInterface $authorRepository = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->statusId = $statusId;
        $this->rating = $rating;
        $this->shelfId = $shelfId;
        $this->userId = $userId;
        $this->coverUrl = $coverUrl;
        $this->epubUrl = $epubUrl;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->physicalPageCount = $physicalPagesCount;
        $this->currentPage = $currentPage;
        $this->authorRepository = $authorRepository;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function getRating(): Rating
    {
        return $this->rating;
    }

    public function getShelfId(): int
    {
        return $this->shelfId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function getEpubUrl(): ?string
    {
        return $this->epubUrl;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPagePhysicalPagesCount(): int
    {
        return $this->physicalPageCount;
    }

    public function getAuthors(): array
    {
        if ($this->authors === null && $this->authorRepository !== null && $this->id !== 0) {
            $this->authors = $this->authorRepository->findByBookId($this->id);
        }
        return $this->authors ?? [];
    }

    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function updateProgress(int $currentPage): void
    {
        $this->currentPage = $currentPage;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setStatusId(int $int): void
    {
        $this->statusId = $int;
    }

    public function getPhysicalPages(): int
    {
        return $this->physicalPageCount;
    }

    public function setCoverUrl(?string $coverUrl): void
    {
        $this->coverUrl = $coverUrl;
    }

    public function setEpubUrl(string $epubUrl): void
    {
        $this->epubUrl = $epubUrl;
    }
}