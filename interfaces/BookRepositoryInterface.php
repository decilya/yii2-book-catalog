<?php
declare(strict_types=1);

namespace app\interfaces;

use app\models\Book;

/**
 * Интерфейс репозитория книг
 */
interface BookRepositoryInterface
{
    /**
     * Возвращает все книги с авторами (жадная загрузка)
     *
     * @return Book[]
     */
    public function findAll(): array;

    /**
     * Находит книгу по ID
     *
     * @param int $id
     * @return Book|null
     */
    public function findById(int $id): ?Book;

    /**
     * Сохраняет книгу и устанавливает связи с авторами
     *
     * @param Book $book
     * @param int[] $authorIds
     * @return bool
     */
    public function saveWithAuthors(Book $book, array $authorIds): bool;

    /**
     * Удаляет книгу по ID (каскадно удаляются связи)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
