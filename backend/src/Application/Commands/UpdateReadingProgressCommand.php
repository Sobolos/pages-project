<?php

namespace App\Application\Commands;

use App\Application\Dto\ReadingProgressDto;
use App\Domain\Entities\ReadingProgress;
use App\Domain\Interfaces\PageCalculatorServiceInterface;
use App\Infrastructure\Repositories\Interfaces\BookRepositoryInterface;
use App\Infrastructure\Repositories\Interfaces\ReadingProgressRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoBookRepository;
use App\Infrastructure\Repositories\Pdo\PdoReadingProgressRepository;
use App\Infrastructure\Services\SimplePageCalculatorService;

class UpdateReadingProgressCommand
{
    private BookRepositoryInterface $bookRepository;
    private PageCalculatorServiceInterface $pageCalculator;
    private ReadingProgressRepositoryInterface $readingProgressRepository;

    public function __construct()
    {
        $this->bookRepository = new PdoBookRepository();
        $this->pageCalculator = new SimplePageCalculatorService();
        $this->readingProgressRepository = new PdoReadingProgressRepository();
    }

    public function execute(int $userId, ReadingProgressDto $dto): void
    {
        $this->bookRepository->findAllWithFilter(['book_id' => $dto->bookId, 'user_id' => $userId]);
        $book = $this->bookRepository->findById($dto->bookId);

        // Если задана только физическая страница — вычисляем процент
        if ($dto->physicalPage !== null && $dto->epubPosition === null) {
            $epubPosition = $this->pageCalculator->physicalToEpub($book, $dto->physicalPage);
        }
        // Если задан только процент — вычисляем физ. страницу
        elseif ($dto->epubPosition !== null && $dto->physicalPage === null) {
            $physicalPage = $this->pageCalculator->epubToPhysical($book, $dto->epubPosition);
        }

        if ($dto->epubPosition === null) {
            throw new \InvalidArgumentException('Either epubPosition or physicalPage must be provided');
        }

        $progress = new ReadingProgress(
            userId: $userId,
            bookId: $dto->bookId,
            epubPosition: $dto->epubPosition,
            updatedAt: new \DateTimeImmutable(),
            physicalPage: $dto->physicalPage
        );

        $this->readingProgressRepository->save($progress);
    }
}