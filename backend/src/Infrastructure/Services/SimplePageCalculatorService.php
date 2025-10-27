<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\Book;
use App\Domain\Interfaces\PageCalculatorServiceInterface;

class SimplePageCalculatorService implements PageCalculatorServiceInterface
{
    public function physicalToEpub(Book $book, int $physicalPage): float
    {
        return ($physicalPage - 1) / max(1, $book->getPagePhysicalPagesCount() - 1);
    }

    public function epubToPhysical(Book $book, float $epubPosition): int
    {
        return (int)round($epubPosition * ($book->getPagePhysicalPagesCount() - 1)) + 1;
    }
}