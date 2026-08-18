<?php
declare(strict_types=1);

namespace app\services;

use app\models\Author;
use app\interfaces\AuthorRepositoryInterface;

/**
 * Сервис для управления авторами
 */
class AuthorService
{
    private AuthorRepositoryInterface $authorRepo;

    public function __construct(AuthorRepositoryInterface $authorRepo)
    {
        $this->authorRepo = $authorRepo;
    }

    /**
     * Возвращает всех авторов
     *
     * @return Author[]
     */
    public function findAll(): array
    {
        return $this->authorRepo->findAll();
    }

    /**
     * Находит автора по ID
     *
     * @param int $id
     * @return Author|null
     */
    public function findById(int $id): ?Author
    {
        return $this->authorRepo->findById($id);
    }

    /**
     * Создаёт нового автора
     *
     * @param Author $author
     * @return bool
     */
    public function create(Author $author): bool
    {
        return $this->authorRepo->save($author);
    }

    /**
     * Обновляет автора
     *
     * @param Author $author
     * @return bool
     */
    public function update(Author $author): bool
    {
        return $this->authorRepo->save($author);
    }

    /**
     * Удаляет автора по ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->authorRepo->delete($id);
    }
}
