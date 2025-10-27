<?php

namespace App\Application\Dto;

readonly class CreateBookDto
{
    public string $title;
    public int $shelfId;
    public int $statusId;
    public int $userId;
    public array $authorsIds;
    public int $physicalPageCount;

    /**
     * @param string $title
     * @param int $shelfId
     * @param int $statusId
     * @param int $userId
     * @param array $authorsIds
     * @param int $physicalPageCount
     */
    public function __construct(
        string $title,
        int $shelfId,
        int $statusId,
        int $userId,
        array $authorsIds,
        int $physicalPageCount
    ) {
        $this->title = $title;
        $this->shelfId = $shelfId;
        $this->statusId = $statusId;
        $this->userId = $userId;
        $this->authorsIds = $authorsIds;
        $this->physicalPageCount = $physicalPageCount;
    }
}