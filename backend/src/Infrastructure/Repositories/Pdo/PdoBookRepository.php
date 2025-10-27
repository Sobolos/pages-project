<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Book;
use App\Domain\Interfaces\Repositories\BookRepositoryInterface;
use App\Infrastructure\Mappers\BookMapper;

class PdoBookRepository extends PdoRepository implements BookRepositoryInterface
{
    private BookMapper $mapper;
    private PdoAuthorRepository $authorRepository;

    public function __construct()
    {
        parent::__construct();
        $this->authorRepository = new PdoAuthorRepository();
        $this->mapper = new BookMapper();
    }

    public function findById(int $id): ?Book
    {
        if ($this->mapper->getFromMap(Book::class, $id)) {
            return $this->mapper->getFromMap(Book::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $book = $this->mapper->toEntity($row);
        $authors = $this->authorRepository->findByBookId($id);
        $book->setAuthors($authors);
        $this->mapper->addToMap($book, $id);

        return $book;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT b.* FROM books b WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND b.user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['status_id'])) {
            $query .= ' AND b.status_id = :status_id';
            $params['status_id'] = $filters['status_id'];
        }

        if (isset($filters['shelf_id'])) {
            $query .= ' AND b.shelf_id = :shelf_id';
            $params['shelf_id'] = $filters['shelf_id'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND b.title ILIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['sort_by'])) {
            $sortField = in_array($filters['sort_by'], ['title', 'rating', 'created_at']) ? $filters['sort_by'] : 'created_at';
            $sortOrder = ($filters['sort_order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY b.$sortField $sortOrder";
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;
        $query .= ' LIMIT :per_page OFFSET :offset';
        $params['per_page'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $books = [];
        foreach ($rows as $row) {
            $book = $this->mapper->toEntity($row);
            $authors = $this->authorRepository->findByBookId($row['id']);
            $book->setAuthors($authors);
            $books[] = $book;
            $this->mapper->addToMap($book, $row['id']);
        }

        return $books;
    }

    public function save(Book $entity, array $authorIds = []): Book
    {
        $data = $this->mapper->toArray($entity);

        if ($this->findById($entity->getId())) {
            $updateData = array_diff_key($data, ['created_at' => true]);
            $stmt = $this->pdo->prepare(
                'UPDATE books SET title = :title, status_id = :status_id, rating = :rating, ' .
                'shelf_id = :shelf_id, user_id = :user_id, cover_url = :cover_url, ' .
                'epub_url = :epub_url, physical_pages = :physical_pages, ' .
                'current_page = :current_page, ' .
                'updated_at = :updated_at WHERE id = :id'
            );

            $stmt->execute($updateData);
        } else {
            $insertData = array_diff_key($data, ['id' => true]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO books (title, status_id, rating, shelf_id, user_id, ' .
                'cover_url, epub_url, physical_pages, current_page, ' .
                'created_at, updated_at) VALUES (:title, :status_id, :rating, :shelf_id, ' .
                ':user_id, :cover_url, :epub_url, :physical_pages, ' .
                ':current_page, :created_at, :updated_at)'
            );
            $stmt->execute($insertData);

            if (!$entity->getId()) {
                $data['id'] = (int)$this->pdo->lastInsertId();
                $entity = $this->mapper->toEntity($data);
                $this->mapper->addToMap($entity, $data['id']);
            }
        }

        if (!empty($authorIds)) {
            $bookId = $entity->getId() ?: (int)$this->pdo->lastInsertId();
            $this->pdo->prepare('DELETE FROM book_authors WHERE book_id = :book_id')
                ->execute(['book_id' => $bookId]);
            foreach ($authorIds as $authorId) {
                $this->pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)')
                    ->execute(['book_id' => $bookId, 'author_id' => $authorId]);
            }
        }

        return $entity;
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM book_authors WHERE book_id = :id')->execute(['id' => $id]);
        $this->pdo->prepare('DELETE FROM books WHERE id = :id')->execute(['id' => $id]);
    }

    public function getTitleById(int $id): ?string
    {
        if ($this->mapper->getFromMap(Book::class, $id)) {
            return $this->mapper->getFromMap(Book::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT title FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $row['title'];
    }
}