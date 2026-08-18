<?php
declare(strict_types=1);

namespace app\interfaces;

use app\models\Author;

/**
 * Интерфейс репозитория авторов
 */
interface AuthorRepositoryInterface
{
    /**
     * Возвращает всех авторов
     *
     * @return Author[]
     */
    public function findAll(): array;

    /**
     * Находит автора по ID
     *
     * @param int $id
     * @return Author|null
     */
    public function findById(int $id): ?Author;

    /**
     * Сохраняет автора (создание или обновление)
     *
     * @param Author $author
     * @return bool
     */
    public function save(Author $author): bool;

    /**
     * Удаляет автора по ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Возвращает ТОП-N авторов по количеству книг за указанный год
     *
     * @param int $year
     * @param int $limit
     * @return array<array{full_name:string, book_count:int}>
     */
    public function getTopAuthorsByYear(int $year, int $limit = 10): array;
}
