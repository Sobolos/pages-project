<?php
namespace App\Application\Commands\Note;

use App\Application\Dto\NoteDto;
use App\Domain\Entities\Note;
use App\Infrastructure\Repositories\Interfaces\NoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoNoteRepository;
use App\Infrastructure\Services\HistoryGeneratorService;
use App\Infrastructure\Services\Validator;
use App\Domain\Interfaces\HistoryGeneratorServiceInterface;

class CreateNoteCommand
{
    private NoteRepositoryInterface $noteRepository;
    private Validator $validator;
    private HistoryGeneratorServiceInterface $historyService;

    public function __construct()
    {
        $this->noteRepository = new PdoNoteRepository();
        $this->validator = new Validator();
        $this->historyService = new HistoryGeneratorService();
    }

    public function execute(NoteDto $noteDto): Note
    {
        $data = [
            'book_id' => $noteDto->bookId,
            'user_id' => $noteDto->userId,
            'content' => $noteDto->content,
        ];

        $errors = $this->validator->validate($data, [
            'book_id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'content' => ['required', 'string'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $note = new Note(
            id: 0,
            bookId: $noteDto->bookId,
            userId: $noteDto->userId,
            content: $noteDto->content,
            createdAt: new \DateTimeImmutable()
        );

        $this->noteRepository->save($note);
        $this->noteRepository->pdo->lastInsertId();

        $this->historyService->generateNoteAddedEvent($note, $noteDto->bookId);

        return $note;
    }
}