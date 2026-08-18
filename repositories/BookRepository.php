<?php
declare(strict_types=1);

namespace app\repositories;

use app\interfaces\BookRepositoryInterface;
use app\models\Book;
use Yii;
use yii\db\Exception;

/**
 * Реализация репозитория книг через ActiveRecord
 */
class BookRepository implements BookRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        return Book::find()->with('authors')->orderBy(['id' => SORT_DESC])->all();
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?Book
    {
        return Book::findOne($id);
    }

    /**
     * @inheritDoc
     */
    public function saveWithAuthors(Book $book, array $authorIds): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::info('Попытка сохранить книгу', 'book');
            if (!$book->save()) {
                Yii::error('Ошибка сохранения книги: ' . json_encode($book->getErrors(), JSON_UNESCAPED_UNICODE), 'book');
                throw new \Exception('Ошибка сохранения книги');
            }
            Yii::info('Книга сохранена, id=' . $book->id, 'book');

            // Удаляем старые связи
            Yii::$app->db->createCommand()
                ->delete('{{%book_author}}', ['book_id' => $book->id])
                ->execute();
            Yii::info('Старые связи удалены', 'book');

            // Добавляем новые связи
            if (!empty($authorIds)) {
                $rows = array_map(fn($id) => [$book->id, (int)$id], $authorIds);
                Yii::$app->db->createCommand()
                    ->batchInsert('{{%book_author}}', ['book_id', 'author_id'], $rows)
                    ->execute();
                Yii::info('Новые связи добавлены: ' . json_encode($rows), 'book');
            }

            $transaction->commit();
            Yii::info('Транзакция закоммичена', 'book');
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка в BookRepository::saveWithAuthors: ' . $e->getMessage(), 'book');
            Yii::error($e->getTraceAsString(), 'book');
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $book = $this->findById($id);
        return $book ? (bool)$book->delete() : false;
    }
}
