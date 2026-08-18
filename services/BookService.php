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
 *
 * Содержит бизнес-логику: создание, обновление, удаление книг,
 * а также уведомление подписчиков о новых книгах.
 */
class BookService
{
    private BookRepositoryInterface $bookRepo;
    private AuthorRepositoryInterface $authorRepo;
    private SubscriptionRepositoryInterface $subRepo;
    private SmsService $smsService;

    /**
     * Конструктор с внедрением зависимостей
     *
     * @param BookRepositoryInterface $bookRepo Репозиторий книг
     * @param AuthorRepositoryInterface $authorRepo Репозиторий авторов
     * @param SubscriptionRepositoryInterface $subRepo Репозиторий подписок
     * @param SmsService $smsService Сервис отправки SMS
     */
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

    /**
     * Возвращает все книги
     *
     * @return Book[]
     */
    public function getAll(): array
    {
        return $this->bookRepo->findAll();
    }

    /**
     * Находит книгу по ID
     *
     * @param int $id
     * @return Book|null
     */
    public function getOne(int $id): ?Book
    {
        return $this->bookRepo->findById($id);
    }

    /**
     * Создаёт новую книгу и отправляет уведомления подписчикам
     *
     * @param Book $book Модель книги
     * @param array $authorIds Массив ID авторов (может быть строками)
     * @param UploadedFile|null $photo Загруженное фото (опционально)
     * @return bool
     */
    public function createBook(Book $book, array $authorIds, ?UploadedFile $photo = null): bool
    {
        // Приводим все ID авторов к целочисленному типу
        $authorIds = array_map('intval', $authorIds);

        // Если передано фото – загружаем
        if ($photo) {
            $book->photo = $this->uploadPhoto($photo);
        }

        // Сохраняем книгу и связи с авторами
        if ($this->bookRepo->saveWithAuthors($book, $authorIds)) {
            // Отправляем уведомления (ошибки не прерывают работу)
            $this->notifySubscribers($authorIds, $book->title);
            return true;
        }
        return false;
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

        if ($photo) {
            // Удаляем старое фото, если есть
            if ($book->photo) {
                $this->deletePhoto($book->photo);
            }
            $book->photo = $this->uploadPhoto($photo);
        }
        return $this->bookRepo->saveWithAuthors($book, $authorIds);
    }

    /**
     * Удаляет книгу и сопутствующее фото
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
     * Уведомляет всех подписчиков авторов о новой книге
     *
     * @param array $authorIds Массив ID авторов
     * @param string $bookTitle Название книги
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
            if (empty($phones)) {
                continue;
            }

            $message = "Новая книга '{$bookTitle}' от автора {$author->full_name} добавлена в каталог!";

            foreach ($phones as $phone) {
                try {
                    $this->smsService->send($phone, $message);
                } catch (\Exception $e) {
                    // Логируем ошибку, но не прерываем выполнение
                    Yii::error("Не удалось отправить SMS на {$phone}: " . $e->getMessage(), 'sms');
                }
            }
        }
    }

    /**
     * Загружает фото на сервер
     *
     * @param UploadedFile $file
     * @return string Относительный URL к файлу
     */
    private function uploadPhoto(UploadedFile $file): string
    {
        $dir = Yii::getAlias('@webroot/uploads/books');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $filename = uniqid() . '.' . $file->extension;
        $file->saveAs($dir . '/' . $filename);
        return '/uploads/books/' . $filename;
    }

    /**
     * Удаляет файл фото с диска
     *
     * @param string $path Относительный путь к файлу
     * @return void
     */
    private function deletePhoto(string $path): void
    {
        $fullPath = Yii::getAlias('@webroot') . $path;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
