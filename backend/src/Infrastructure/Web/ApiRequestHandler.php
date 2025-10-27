<?php

namespace App\Infrastructure\Web;

use App\Application\Commands\Author\BatchCreateAuthorCommand;
use App\Application\Commands\Author\CreateAuthorCommand;
use App\Application\Commands\Author\DeleteAuthorCommand;
use App\Application\Commands\Author\UpdateAuthorCommand;
use App\Application\Commands\Books\CreateBookCommand;
use App\Application\Commands\Books\RemoveBookCoverCommand;
use App\Application\Commands\Books\RemoveBookEpubCommand;
use App\Application\Commands\Books\UpdateBookCommand;
use App\Application\Commands\Books\DeleteBookCommand;
use App\Application\Commands\Books\UpdateBookStatusCommand;
use App\Application\Commands\Books\UploadBookCoverCommand;
use App\Application\Commands\Books\UploadBookEpubCommand;
use App\Application\Commands\Note\CreateNoteCommand;
use App\Application\Commands\Note\DeleteNoteCommand;
use App\Application\Commands\Note\UpdateNoteCommand;
use App\Application\Commands\Quote\CreateQuoteCommand;
use App\Application\Commands\Quote\DeleteQuoteCommand;
use App\Application\Commands\Quote\UpdateQuoteCommand;
use App\Application\Commands\Shelf\CreateShelfCommand;
use App\Application\Commands\Shelf\DeleteShelfCommand;
use App\Application\Commands\Shelf\UpdateShelfCommand;
use App\Application\Commands\Status\CreateStatusCommand;
use App\Application\Commands\Status\DeleteStatusCommand;
use App\Application\Commands\Status\ReorderStatusCommand;
use App\Application\Commands\Status\UpdateStatusCommand;
use App\Application\Commands\UpdateReadingProgressCommand;
use App\Application\Commands\User\LoginUserCommand;
use App\Application\Commands\User\RegisterUserCommand;
use App\Application\Dto\AuthorDto;
use App\Application\Dto\BatchAuthorDto;
use App\Application\Dto\CreateBookDto;
use App\Application\Dto\LoginDto;
use App\Application\Dto\NoteDto;
use App\Application\Dto\QuoteDto;
use App\Application\Dto\ReadingProgressDto;
use App\Application\Dto\ReorderStatusesDto;
use App\Application\Dto\ShelfDto;
use App\Application\Dto\StatusDto;
use App\Application\Dto\UpdateStatusDto;
use App\Application\Dto\UpdateBookDto;
use App\Application\Dto\UpdateBookStatusDto;
use App\Application\Dto\UserDto;
use App\Application\Queries\GetAuthorsQuery;
use App\Application\Queries\GetBooksQuery;
use App\Application\Queries\GetNotesQuery;
use App\Application\Queries\GetQuotesQuery;
use App\Application\Queries\GetShelvesQuery;
use App\Application\Queries\GetStatusesQuery;
use App\Config\ProtectedRoutes;
use App\Domain\Entities\Author;
use App\Domain\ValueObjects\Color;
use App\Infrastructure\Services\AuthService;

class ApiRequestHandler
{
    private AuthService $authService;
    private GetAuthorsQuery $authorsQuery;
    private CreateAuthorCommand $createAuthorCommand;
    private UpdateAuthorCommand $updateAuthorCommand;
    private DeleteAuthorCommand $deleteAuthorCommand;
    private CreateStatusCommand $createStatusCommand;
    private UpdateStatusCommand $updateStatusCommand;
    private DeleteStatusCommand $deleteStatusCommand;
    private GetStatusesQuery $statusesQuery;
    private GetNotesQuery $notesQuery;
    private CreateNoteCommand $createNoteCommand;
    private DeleteNoteCommand $deleteNoteCommand;
    private UpdateNoteCommand $updateNoteCommand;
    private GetQuotesQuery $quotesQuery;
    private CreateQuoteCommand $createQuoteCommand;
    private DeleteQuoteCommand $deleteQuoteCommand;
    private UpdateQuoteCommand $updateQuoteCommand;
    private GetShelvesQuery $shelvesQuery;
    private CreateShelfCommand $createShelfCommand;
    private DeleteShelfCommand $deleteShelfCommand;
    private UpdateShelfCommand $updateShelfCommand;
    private GetBooksQuery $booksQuery;
    private CreateBookCommand $createBookCommand;
    private DeleteBookCommand $deleteBookCommand;
    private UpdateBookCommand $updateBookCommand;
    private BatchCreateAuthorCommand $batchCreateAuthorCommand;
    private UpdateReadingProgressCommand $updateReadingProgressCommand;
    private UploadBookCoverCommand $uploadBookCoverCommand;
    private UploadBookEpubCommand  $uploadBookEpubCommand;
    private RemoveBookCoverCommand $removeBookCoverCommand;
    private RemoveBookEpubCommand $removeBookEpubCommand;
    private UpdateBookStatusCommand $updateBookStatusCommand;
    private ReorderStatusCommand $reorderStatusesCommand;
    private RegisterUserCommand $registerUserCommand;
    private LoginUserCommand $loginUserCommand;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->loginUserCommand = new LoginUserCommand();
        $this->registerUserCommand = new RegisterUserCommand();

        $this->createAuthorCommand = new CreateAuthorCommand();
        $this->batchCreateAuthorCommand = new BatchCreateAuthorCommand();
        $this->updateAuthorCommand = new UpdateAuthorCommand();
        $this->deleteAuthorCommand = new DeleteAuthorCommand();
        $this->authorsQuery = new GetAuthorsQuery();

        $this->createStatusCommand = new CreateStatusCommand();
        $this->updateStatusCommand = new UpdateStatusCommand();
        $this->deleteStatusCommand = new DeleteStatusCommand();
        $this->statusesQuery = new GetStatusesQuery();
        $this->reorderStatusesCommand = new ReorderStatusCommand();

        $this->createNoteCommand = new CreateNoteCommand();
        $this->deleteNoteCommand = new DeleteNoteCommand();
        $this->updateNoteCommand = new UpdateNoteCommand();
        $this->notesQuery = new GetNotesQuery();

        $this->createQuoteCommand = new CreateQuoteCommand();
        $this->deleteQuoteCommand = new DeleteQuoteCommand();
        $this->updateQuoteCommand = new UpdateQuoteCommand();
        $this->quotesQuery = new GetQuotesQuery();

        $this->createShelfCommand = new CreateShelfCommand();
        $this->deleteShelfCommand = new DeleteShelfCommand();
        $this->updateShelfCommand = new UpdateShelfCommand();
        $this->shelvesQuery = new GetShelvesQuery();

        $this->createBookCommand = new CreateBookCommand();
        $this->deleteBookCommand = new DeleteBookCommand();
        $this->updateBookCommand = new UpdateBookCommand();
        $this->booksQuery = new GetBooksQuery();
        $this->uploadBookCoverCommand = new UploadBookCoverCommand();
        $this->uploadBookEpubCommand = new UploadBookEpubCommand();
        $this->removeBookCoverCommand = new RemoveBookCoverCommand();
        $this->removeBookEpubCommand = new RemoveBookEpubCommand();

        $this->updateReadingProgressCommand = new UpdateReadingProgressCommand();
        $this->updateBookStatusCommand = new UpdateBookStatusCommand();
    }

    public function handle(string $uri, string $method, array $data): array
    {
        try {
            // Проверка авторизации для защищённых роутов
            $userId = null;

            $isProtected = false;
            foreach (ProtectedRoutes::ROUTES as $pattern) {
                if (preg_match($pattern, $uri)) {
                    $isProtected = true;
                    break;
                }
            }

            if ($isProtected) {
                $headers = getallheaders();
                $token = $headers['Authorization'] ?? '';
                $token = preg_replace('/^Bearer\s+/', '', $token);
                $userId = $this->authService->validateToken($token);
                if (!$userId) {
                    return ['error' => 'Unauthorized', 'uri' => $uri, 'code' => 401];
                }
            }

            ###>>> Auth
            if (preg_match('#^/api/auth/register#', $uri) && $method === 'POST') {
                $userDto = new UserDto(
                    name: $data['name'],
                    email: $data['email'],
                    password: $data['password']
                );

                try {
                    $tokens = $this->registerUserCommand->execute($userDto);
                } catch (\Throwable $e) {
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }


                return [
                    'status' => 'success',
                    'data' => $tokens
                ];
            }

            if (preg_match('#^/api/auth/login#', $uri) && $method === 'POST') {
                $loginDto = new LoginDto(
                    name: $data['name'],
                    password: $data['password'],
                );

                $tokens = $this->loginUserCommand->execute($loginDto);

                return [
                    'status' => 'success',
                    'data' => $tokens
                ];
            }
            #<<< Auth

            ###>>> Authors
            if (preg_match('#^/api/authors#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $authors = $this->authorsQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($author) => [
                            'id' => $author->getId(),
                            'name' => $author->getName(),
                        ],
                        $authors
                    )
                ];
            }

            if (preg_match('#^/api/authors#', $uri) && $method === 'POST') {
                $authorDto = new AuthorDto(
                    name: $data['name'],
                    userId: $userId,
                );

                $author = $this->createAuthorCommand->execute($authorDto);

                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $author->getId(),
                        'name' => $author->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/batch-authors#', $uri) && $method === 'POST') {
                $dtos = [];

                foreach ($data['names'] as $author) {
                    $dtos[] = new AuthorDto(
                        name: $author,
                        userId: (int)$userId,
                    );
                }

                $batchAuthorDtos = new BatchAuthorDto($dtos, $userId);

                $authors = $this->batchCreateAuthorCommand->execute($batchAuthorDtos);

                $return = array_map(function (Author $author) {
                    return ['id' => $author->getId(), 'name' => $author->getName()];
                }, $authors);

                return [
                    'status' => 'success',
                    'data' => $return
                ];
            }

            if (preg_match('#^/api/authors/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $authorDto = new AuthorDto(
                    name: $data['name'],
                    userId: $userId,
                    id: $matches[1],
                );

                $author = $this->updateAuthorCommand->execute($authorDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $author->getId(),
                        'name' => $author->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/authors/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteAuthorCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }
            ###<<< Authors

            ###>>> Statuses
            if (preg_match('#^/api/statuses#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $statuses = $this->statusesQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($status) => [
                            'id' => $status->getId(),
                            'name' => $status->getName(),
                            'color' => $status->getColor()->getValue(),
                            'hide_from_agile' => $status->isHiddenFromAgile(),
                            'position' => $status->getPosition(),
                        ],
                        $statuses
                    )
                ];
            }

            if (preg_match('#^/api/statuses#', $uri) && $method === 'POST') {
                $color = new Color($data['color']);

                $statusDto = new StatusDto(
                    name: $data['name'],
                    userId: $userId,
                    color: $color,
                    hide: $data['hide_from_agile'],
                    position: $data['position'],
                );

                $status = $this->createStatusCommand->execute($statusDto);

                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $status->getId(),
                        'name' => $status->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/statuses/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $color = new Color($data['color']);

                $statusDto = new UpdateStatusDto(
                    name: $data['name'],
                    userId: $userId,
                    color: $color,
                    hide: $data['hide_from_agile'],
                    id: (int)$matches[1],
                );

                $status = $this->updateStatusCommand->execute($statusDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $status->getId(),
                        'name' => $status->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/statuses/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteStatusCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }

            if (preg_match('#^/api/reorder-statuses#', $uri, $matches) && $method === 'POST')
            {
                $reorderStatusDto = new ReorderStatusesDto($data);

                $this->reorderStatusesCommand->execute($reorderStatusDto, $userId);

                return ['status' => 'success', 'data' => []];
            }

            ###<<< Statuses

            ###<<< Shelves
            if (preg_match('#^/api/shelves#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $shelves = $this->shelvesQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($shelf) => [
                            'id' => $shelf->getId(),
                            'name' => $shelf->getName(),
                        ],
                        $shelves
                    )
                ];
            }

            if (preg_match('#^/api/shelves#', $uri) && $method === 'POST') {
                $shelfDto = new ShelfDto(
                    name: $data['name'],
                    userId: $userId,
                );

                $shelf = $this->createShelfCommand->execute($shelfDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $shelf->getId(),
                        'name' => $shelf->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/shelves/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $shelfDto = new ShelfDto(
                    name: $data['name'],
                    userId: $userId,
                    id: (int)$matches[1],
                );

                $shelf = $this->updateShelfCommand->execute($shelfDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $shelf->getId(),
                        'name' => $shelf->getName(),
                    ]
                ];
            }

            if (preg_match('#^/api/shelves/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteShelfCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }
            ###<<< Shelves

            ###>>> Notes
            if (preg_match('#^/api/notes#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $notes = $this->notesQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($note) => [
                            'id' => $note->getId(),
                            'book_id' => $note->getBookId(),
                            'content' => $note->getContent(),
                            'created_at' => $note->getCreatedAt()->format('Y-m-d H:i:s'),
                        ],
                        $notes
                    ),
                    'pagination' => [
                        'page' => (int)($_GET['page'] ?? 1),
                        'per_page' => (int)($_GET['per_page'] ?? 10),
                    ]
                ];
            }

            if (preg_match('#^/api/notes#', $uri) && $method === 'POST') {
                $noteDto = new NoteDto(
                    content: $data['content'],
                    userId: $userId,
                    bookId: $data['book_id'],
                );

                $note = $this->createNoteCommand->execute($noteDto);

                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $note->getId(),
                        'content' => $note->getContent(),
                    ]
                ];
            }

            if (preg_match('#^/api/notes/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $noteDto = new NoteDto(
                    content: $data['content'],
                    userId: $userId,
                    id: (int)$matches[1],
                );

                $note = $this->updateNoteCommand->execute($noteDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $note->getId(),
                        'content' => $note->getContent(),
                    ]
                ];
            }

            if (preg_match('#^/api/notes/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteNoteCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }

            ###>>> Quotes
            if (preg_match('#^/api/quotes#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $quotes = $this->quotesQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($quote) => [
                            'id' => $quote->getId(),
                            'book_id' => $quote->getBookId(),
                            'content' => $quote->getContent(),
                            'page_number' => $quote->getPageNumber(),
                            'created_at' => $quote->getCreatedAt()->format('Y-m-d H:i:s'),
                        ],
                        $quotes
                    ),
                    'pagination' => [
                        'page' => (int)($_GET['page'] ?? 1),
                        'per_page' => (int)($_GET['per_page'] ?? 10),
                    ]
                ];
            }

            if (preg_match('#^/api/quotes#', $uri) && $method === 'POST') {
                $quoteDto = new QuoteDto(
                    content: (int)($data['book_id'] ?? 0),
                    userId: $userId,
                    pageNumber: (int)($data['page_number']),
                    bookId: $data['book_id'],
                );

                $quote = $this->createQuoteCommand->execute($quoteDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $quote->getId(),
                        'content' => $quote->getContent(),
                        'page_number' => $quote->getPageNumber(),
                    ]
                ];
            }

            if (preg_match('#^/api/quotes/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $quoteDto = new QuoteDto(
                    content: (int)($data['book_id'] ?? 0),
                    userId: $userId,
                    pageNumber: (int)($data['page_number']),
                    bookId: $data['book_id'],
                );

                $quote = $this->updateQuoteCommand->execute($quoteDto);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $quote->getId(),
                        'content' => $quote->getContent(),
                        'page_number' => $quote->getPageNumber(),
                    ]
                ];
            }

            if (preg_match('#^/api/quotes/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteQuoteCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }
            ###<<< Quotes

            ###>>> Books
            if (preg_match('#^/api/books#', $uri) && $method === 'GET') {
                $filters = array_merge($_GET, ['user_id' => $userId]);
                $books = $this->booksQuery->execute($filters);
                return [
                    'status' => 'success',
                    'data' => array_map(
                        fn($book) => [
                            'id' => $book->getId(),
                            'title' => $book->getTitle(),
                            'rating' => $book->getRating()->getValue(),
                            'status_id' => $book->getStatusId(),
                            'shelf_id' => $book->getShelfId(),
                            'user_id' => $book->getUserId(),
                            'authors' => array_map(
                                fn($author) => ['id' => $author->getId(), 'name' => $author->getName()],
                                $book->getAuthors()
                            ),
                            'current_page' => $book->getCurrentPage(),
                            'physical_pages' => $book->getPhysicalPages(),
                            'epub_url' => $book->getEpubUrl(),
                        ],
                        $books
                    ),
                    'pagination' => [
                        'page' => (int)($_GET['page'] ?? 1),
                        'per_page' => (int)($_GET['per_page'] ?? 10),
                    ]
                ];
            }

            if (preg_match('#^/api/cover-book/(\d+)#', $uri) && $method === 'POST') {
                $bookId = (int)$matches[1];

                if (empty($_FILES['cover']['tmp_name'])) {
                    http_response_code(400);
                    return ['error' => 'Cover file is required'];
                }

                $this->uploadBookCoverCommand->execute($userId, $bookId, $_FILES['cover']);
            }

            if (preg_match('#^/api/epub-book/(\d+)#', $uri) && $method === 'POST') {
                $bookId = (int)$matches[1];

                if (empty($_FILES['epub']['tmp_name'])) {
                    http_response_code(400);
                    return ['error' => 'Epub file is required'];
                }

                $this->uploadBookEpubCommand->execute($userId, $bookId, $_FILES['epub']);
            }

            if (preg_match('#^/api/cover-book/(\d+)#', $uri) && $method === 'DELETE') {
                $bookId = (int)$matches[1];

                $this->removeBookCoverCommand->execute($userId, $bookId);
            }

            if (preg_match('#^/api/epub-book/(\d+)#', $uri) && $method === 'DELETE') {
                $bookId = (int)$matches[1];

                $this->removeBookEpubCommand->execute($userId, $bookId);
            }

            if (preg_match('#^/api/books#', $uri) && $method === 'POST') {
                $createBookDto = new CreateBookDto(
                    title: $data['title'],
                    shelfId: $data['shelf_id'],
                    statusId: $data['status_id'],
                    userId: $userId,
                    authorsIds: $data['selected_authors'],
                    physicalPageCount: $data['physical_page_count'],
                );

                $book = $this->createBookCommand->execute($userId, $createBookDto);

                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                    ]
                ];
            }

            if (preg_match('#^/api/books/(\d+)#', $uri, $matches) && $method === 'PUT') {
                $updateBookDto = new UpdateBookDto(
                    id: (int)$matches[1],
                    title: $data['title'],
                    rating: $data['rating'],
                    shelfId: $data['shelf_id'],
                    statusId: $data['status_id'],
                    userId: $data['user_id'],
                    coverUrl: $data['cover_url'],
                    epubUrl: $data['epub_url'],
                    authorsIds: $data['selected_authors'],
                    physicalPageCount: $data['physical_page_count'],
                    currentPage: $data['current_page'],
                    createdAt: new \DateTimeImmutable($data['created_at']),
                );

                $book = $this->updateBookCommand->execute($updateBookDto, $userId);
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $book->getId(),
                        'title' => $book->getTitle(),
                    ]
                ];
            }

            if (preg_match('#^/api/books/(\d+)#', $uri, $matches) && $method === 'DELETE') {
                $this->deleteBookCommand->execute((int)$matches[1], $userId);
                return ['status' => 'success', 'data' => []];
            }

            if (preg_match('#^/api/book-status/(\d+)#', $uri, $matches) && $method === 'POST') {
                $updateStatusDto = new UpdateBookStatusDto(
                    bookId: $matches[1],
                    status: $data['status'],
                );

                $this->updateBookStatusCommand->execute($updateStatusDto, $userId);

                return ['status' => 'success', 'data' => []];
            }

            ###<<< Books

            if (preg_match('#^/api/reading-progress/(\d+)#', $uri, $matches) && $method === 'POST') {
                $dto = new ReadingProgressDto(
                    (int)$matches[1],
                    $data['epubPosition'],
                    $data['physicalPage'],
                );
                $this->updateReadingProgressCommand->execute($userId, $dto);

                return ['status' => 'success', 'data' => []];
            }

            return ['error' => 'Not Found', 'uri' => $uri, 'code' => 404];
        } catch (\InvalidArgumentException $e) {
            return ['error' => 'Validation failed', 'details' => json_decode($e->getMessage(), true), 'code' => 400, 'trace' => $e->getTraceAsString()];
        } catch (\RuntimeException $e) {
            return ['error' => $e->getMessage(), 'trace' => $e->getTrace(), 'code' => 403];
        }
    }
}