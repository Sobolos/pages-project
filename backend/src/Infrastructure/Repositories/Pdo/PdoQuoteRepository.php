<?php

namespace App\Infrastructure\Repositories\Pdo;

use App\Domain\Entities\Quote;
use App\Domain\Interfaces\Repositories\QuoteRepositoryInterface;
use App\Infrastructure\Mappers\QuoteMapper;

class PdoQuoteRepository extends PdoRepository implements QuoteRepositoryInterface
{
    private QuoteMapper $mapper;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = new QuoteMapper();
    }

    public function findById(int $id): ?Quote
    {
        if ($this->mapper->getFromMap(Quote::class, $id)) {
            return $this->mapper->getFromMap(Quote::class, $id);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM quotes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $quote = $this->mapper->toEntity($row);
        $this->mapper->addToMap($quote, $id);
        return $quote;
    }

    public function findAllWithFilter(array $filters): array
    {
        $query = 'SELECT * FROM quotes WHERE 1=1';
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= ' AND user_id = :user_id';
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['book_id'])) {
            $query .= ' AND book_id = :book_id';
            $params['book_id'] = $filters['book_id'];
        }

        if (isset($filters['search'])) {
            $query .= ' AND content LIKE :search';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['page_number'])) {
            $query .= ' AND page_number = :page_number';
            $params['page_number'] = $filters['page_number'];
        }

        if (isset($filters['sort_by'])) {
            $sortField = in_array($filters['sort_by'], ['page_number', 'created_at', 'content']) ? $filters['sort_by'] : 'page_number';
            $sortOrder = ($filters['sort_order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY $sortField $sortOrder";
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

        $quotes = [];
        foreach ($rows as $row) {
            $quote = $this->mapper->toEntity($row);
            $quotes[] = $quote;
            $this->mapper->addToMap($quote, $row['id']);
        }

        return $quotes;
    }

    public function save(object $entity): void
    {
        if (!$entity instanceof Quote) {
            throw new \InvalidArgumentException('Entity must be an instance of Quote');
        }

        $data = $this->mapper->toArray($entity);
        if ($this->findById($entity->getId())) {
            $updateData = array_diff_key($data, ['created_at' => true]);
            $stmt = $this->pdo->prepare(
                'UPDATE quotes SET book_id = :book_id, user_id = :user_id, content = :content, ' .
                'page_number = :page_number, updated_at = :updated_at WHERE id = :id'
            );
            $stmt->execute($updateData);
        } else {
            $insertData = array_diff_key($data, ['id' => true]);
            $stmt = $this->pdo->prepare(
                'INSERT INTO quotes (book_id, user_id, content, page_number, created_at, updated_at) ' .
                'VALUES (:book_id, :user_id, :content, :page_number, :created_at, :updated_at)'
            );
            $stmt->execute($insertData);
            if (!$entity->getId()) {
                $data['id'] = (int)$this->pdo->lastInsertId();
                $this->mapper->addToMap($this->mapper->toEntity($data), $data['id']);
            }
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM quotes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}