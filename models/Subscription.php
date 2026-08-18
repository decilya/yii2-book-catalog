<?php
declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель подписки на уведомления о новых книгах автора
 *
 * @property int $id
 * @property int $author_id
 * @property string $phone
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Author $author
 */
class Subscription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%subscription}}';
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
            [['author_id', 'phone'], 'required'],
            [['author_id'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'match', 'pattern' => '/^\+?[0-9\s\-]{10,20}$/', 'message' => 'Неверный формат телефона'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'author_id' => 'Автор',
            'phone' => 'Номер телефона',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Связь с автором
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
