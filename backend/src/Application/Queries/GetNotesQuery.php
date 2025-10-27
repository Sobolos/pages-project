<?php
namespace App\Application\Queries;

use App\Domain\Interfaces\Repositories\NoteRepositoryInterface;
use App\Infrastructure\Repositories\Pdo\PdoNoteRepository;

class GetNotesQuery
{
    private NoteRepositoryInterface $noteRepository;

    public function __construct()
    {
        $this->noteRepository = new PdoNoteRepository();
    }

    public function execute(array $filters): array
    {
        return $this->noteRepository->findAllWithFilter($filters);
    }
}