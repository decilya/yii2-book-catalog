<?php
declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель автора
 *
 * @property int $id
 * @property string $full_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Book[] $books
 */
class Author extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%author}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['full_name'], 'required'],
            [['full_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'full_name' => 'ФИО',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Связь с книгами через промежуточную таблицу book_author
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBooks(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }
}
