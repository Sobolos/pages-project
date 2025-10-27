<?php
namespace App\Application\Commands\Note;

use App\Application\Dto\NoteDto;
use App\Domain\Entities\Note;
use App\Infrastructure\Repositories\Pdo\PdoNoteRepository;
use App\Infrastructure\Repositories\Interfaces\NoteRepositoryInterface;
use App\Infrastructure\Services\Validator;

class UpdateNoteCommand
{
    private NoteRepositoryInterface $noteRepository;
    private Validator $validator;

    public function __construct()
    {
        $this->noteRepository = new PdoNoteRepository();
        $this->validator = new Validator();
    }

    public function execute(NoteDto $noteDto): Note
    {
        $data = [
            'id' => $noteDto->id,
            'user_id' => $noteDto->userId,
            'content' => $noteDto->content,
        ];

        $errors = $this->validator->validate($data, [
            'id' => ['required', 'positive_int'],
            'user_id' => ['required', 'positive_int'],
            'content' => ['required', 'string'],
        ]);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(json_encode($errors));
        }

        $note = $this->noteRepository->findById($noteDto->id);
        if (!$note || $note->getUserId() !== $noteDto->userId) {
            throw new \RuntimeException('Note not found or access denied');
        }

        $note->updateContent($noteDto->content);
        $this->noteRepository->save($note);
        return $note;
    }
}