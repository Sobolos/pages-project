<?php
namespace App\Application\Commands\Note;

use App\Infrastructure\Repositories\Interfaces\NoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoNoteRepository;

class DeleteNoteCommand
{
    private NoteRepositoryInterface $noteRepository;

    public function __construct()
    {
        $this->noteRepository = new PdoNoteRepository();
    }

    public function execute(int $id, int $userId): void
    {
        $note = $this->noteRepository->findById($id);
        if (!$note || $note->getUserId() !== $userId) {
            throw new \RuntimeException('Note not found or access denied');
        }
        $this->noteRepository->delete($id);
    }
}