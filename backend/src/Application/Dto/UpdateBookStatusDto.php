<?php

namespace App\Application\Dto;

readonly class UpdateBookStatusDto
{
    public int $bookId;
    public int $status;

    /**
     * @param int $bookId
     * @param int $status
     */
    public function __construct(int $bookId, int $status)
    {
        $this->bookId = $bookId;
        $this->status = $status;
    }
}