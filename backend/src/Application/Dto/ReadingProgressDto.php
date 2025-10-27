<?php

namespace App\Application\Dto;

readonly class ReadingProgressDto
{
    public int $bookId;
    public ?float $epubPosition;
    public ?int $physicalPage;

    public function __construct(
        int $bookId,
        ?float $epubPosition,
        ?int $physicalPage = null
    ) {
        $this->bookId = $bookId;

        if ($epubPosition === null && $physicalPage === null) {
            throw new \InvalidArgumentException('Either epubPosition or physicalPage must be provided');
        }
        if ($epubPosition !== null && ($epubPosition < 0.0 || $epubPosition > 1.0)) {
            throw new \InvalidArgumentException('epubPosition must be between 0.0 and 1.0');
        }
        if ($physicalPage !== null && $physicalPage < 1) {
            throw new \InvalidArgumentException('physicalPage must be >= 1');
        }
        $this->epubPosition = $epubPosition;
        $this->physicalPage = $physicalPage;
    }

}