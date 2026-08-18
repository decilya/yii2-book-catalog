<?php
declare(strict_types=1);

namespace app\services;

use app\interfaces\BookRepositoryInterface;
use app\interfaces\AuthorRepositoryInterface;
use app\interfaces\SubscriptionRepositoryInterface;
use app\models\Book;
use Yii;
use yii\web\UploadedFile;

/**
 * Сервис управления книгами
 */
class BookService
{
    private BookRepositoryInterface $bookRepo;
    private AuthorRepositoryInterface $authorRepo;
    private SubscriptionRepositoryInterface $subRepo;
    private SmsService $smsService;

    public function __construct(
        BookRepositoryInterface $bookRepo,
        AuthorRepositoryInterface $authorRepo,
        SubscriptionRepositoryInterface $subRepo,
        SmsService $smsService
    ) {
        $this->bookRepo = $bookRepo;
        $this->authorRepo = $authorRepo;
        $this->subRepo = $subRepo;
        $this->smsService = $smsService;
    }

    public function getAll(): array
    {
        return $this->bookRepo->findAll();
    }

    public function getOne(int $id): ?Book
    {
        return $this->bookRepo->findById($id);
    }

    /**
     * Создаёт новую книгу
     *
     * @param Book $book
     * @param array $authorIds
     * @param UploadedFile|null $photo
     * @return bool
     */
    public function createBook(Book $book, array $authorIds, ?UploadedFile $photo = null): bool
    {
        $authorIds = array_map('intval', $authorIds);

        // 1. Сначала сохраняем книгу и связи в БД
        if (!$this->bookRepo->saveWithAuthors($book, $authorIds)) {
            return false;
        }

        // 2. Если фото передано, загружаем его и обновляем поле только после успешной загрузки
        if ($photo && !$photo->getHasError()) {
            try {
                $book->photo = $this->uploadPhoto($photo);
                $book->updateAttributes(['photo']); // обновляем только поле photo
            } catch (\Exception $e) {
                Yii::error('Ошибка загрузки фото: ' . $e->getMessage(), 'book');
                // Книга уже сохранена, но фото не загружено — возвращаем false, чтобы сигнализировать о проблеме
                // Можно также удалить только что созданную книгу, но для тестового задания достаточно вернуть false
                return false;
            }
        }

        $this->notifySubscribers($authorIds, $book->title);
        return true;
    }

    /**
     * Обновляет книгу
     *
     * @param Book $book
     * @param array $authorIds
     * @param UploadedFile|null $photo
     * @return bool
     */
    public function updateBook(Book $book, array $authorIds, ?UploadedFile $photo = null): bool
    {
        $authorIds = array_map('intval', $authorIds);

        // 1. Сначала обновляем данные книги и связи в БД
        if (!$this->bookRepo->saveWithAuthors($book, $authorIds)) {
            return false;
        }

        // 2. Если передано новое фото, удаляем старое и загружаем новое только после успешного обновления БД
        if ($photo && !$photo->getHasError()) {
            try {
                // Сохраняем старый путь для удаления в случае успешной загрузки нового
                $oldPhoto = $book->photo;
                $book->photo = $this->uploadPhoto($photo);
                $book->updateAttributes(['photo']);

                // Теперь можно безопасно удалить старое фото
                if ($oldPhoto) {
                    $this->deletePhoto($oldPhoto);
                }
            } catch (\Exception $e) {
                Yii::error('Ошибка загрузки фото при обновлении: ' . $e->getMessage(), 'book');
                return false;
            }
        }

        return true;
    }

    /**
     * Удаляет книгу
     *
     * @param int $id
     * @return bool
     */
    public function deleteBook(int $id): bool
    {
        $book = $this->bookRepo->findById($id);
        if ($book && $book->photo) {
            $this->deletePhoto($book->photo);
        }
        return $this->bookRepo->delete($id);
    }

    /**
     * Загружает фото на сервер с проверкой MIME-типа и размера
     *
     * @param UploadedFile $file
     * @return string Относительный URL к файлу
     * @throws \Exception
     */
    private function uploadPhoto(UploadedFile $file): string
    {
        $dir = Yii::getAlias('@webroot/uploads/books');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Проверка MIME-типа
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file->type, $allowedMime, true)) {
            throw new \Exception('Недопустимый тип файла. Разрешены только JPG, PNG, GIF.');
        }

        // Проверка размера (максимум 5 МБ)
        $maxSize = 5 * 1024 * 1024;
        if ($file->size > $maxSize) {
            throw new \Exception('Файл слишком большой. Максимальный размер: 5 МБ.');
        }

        $filename = uniqid() . '.' . $file->extension;
        if (!$file->saveAs($dir . '/' . $filename)) {
            throw new \Exception('Не удалось сохранить файл на сервере.');
        }

        return '/uploads/books/' . $filename;
    }

    /**
     * Удаляет файл фото с диска
     *
     * @param string $path
     * @return void
     */
    private function deletePhoto(string $path): void
    {
        $fullPath = Yii::getAlias('@webroot') . $path;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    /**
     * Уведомляет подписчиков авторов
     *
     * @param array $authorIds
     * @param string $bookTitle
     * @return void
     */
    private function notifySubscribers(array $authorIds, string $bookTitle): void
    {
        foreach ($authorIds as $authorId) {
            $author = $this->authorRepo->findById((int)$authorId);
            if (!$author) {
                continue;
            }
            $phones = $this->subRepo->getPhonesByAuthorId((int)$authorId);
            $message = "Новая книга '{$bookTitle}' от автора {$author->full_name} добавлена в каталог!";
            foreach ($phones as $phone) {
                try {
                    $this->smsService->send($phone, $message);
                } catch (\Exception $e) {
                    Yii::error("Не удалось отправить SMS на {$phone}: " . $e->getMessage(), 'sms');
                }
            }
        }
    }
}
