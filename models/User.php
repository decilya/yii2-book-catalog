<?php
declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Модель пользователя для авторизации
 *
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['username', 'password_hash', 'auth_key'], 'required'],
            [['username'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): ?self
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null; // не используется
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Находит пользователя по имени
     *
     * @param string $username
     * @return self|null
     */
    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Проверяет пароль
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Устанавливает хеш пароля
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Генерирует ключ авторизации
     *
     * @return void
     */
    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}
