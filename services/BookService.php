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

        // Обрабатываем фото, если оно передано
        if ($photo && !$photo->getHasError()) {
            try {
                $book->photo = $this->uploadPhoto($photo);
            } catch (\Exception $e) {
                Yii::error('Ошибка загрузки фото: ' . $e->getMessage(), 'book');
                // Не сохраняем книгу, если фото не загрузилось
                return false;
            }
        }

        if ($this->bookRepo->saveWithAuthors($book, $authorIds)) {
            $this->notifySubscribers($authorIds, $book->title);
            return true;
        }
        return false;
    }

    public function updateBook(Book $book, array $authorIds, ?UploadedFile $photo = null): bool
    {
        $authorIds = array_map('intval', $authorIds);

        if ($photo && !$photo->getHasError()) {
            try {
                // Удаляем старое фото, если есть
                if ($book->photo) {
                    $this->deletePhoto($book->photo);
                }
                $book->photo = $this->uploadPhoto($photo);
            } catch (\Exception $e) {
                Yii::error('Ошибка загрузки фото при обновлении: ' . $e->getMessage(), 'book');
                return false;
            }
        }

        return $this->bookRepo->saveWithAuthors($book, $authorIds);
    }

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
