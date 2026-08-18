<?php
declare(strict_types=1);

namespace app\repositories;

use app\interfaces\AuthorRepositoryInterface;
use app\models\Author;

/**
 * Реализация репозитория авторов через ActiveRecord
 */
class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        return Author::find()->all();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Author
    {
        return Author::findOne($id);
    }

    /**
     * @inheritDoc
     */
    public function save(Author $author): bool
    {
        return $author->save();
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $author = $this->findById($id);
        return $author ? (bool)$author->delete() : false;
    }

    /**
     * @inheritDoc
     */
    public function getTopAuthorsByYear(int $year, int $limit = 10): array
    {
        return Author::find()
            ->select(['author.full_name', 'COUNT(book_author.book_id) AS book_count'])
            ->innerJoin('book_author', 'author.id = book_author.author_id')
            ->innerJoin('book', 'book.id = book_author.book_id AND book.year = :year', [':year' => $year])
            ->groupBy('author.id, author.full_name')
            ->orderBy(['book_count' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
    }
}
