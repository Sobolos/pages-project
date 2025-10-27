<?php

namespace App\Domain\Interfaces;

use App\Domain\Entities\Book;

interface PageCalculatorServiceInterface
{
    // Переводит физическую страницу → процент
    public function physicalToEpub(Book $book, int $physicalPage): float;

    // Переводит процент → физическую страницу
    public function epubToPhysical(Book $book, float $epubPosition): int;
}