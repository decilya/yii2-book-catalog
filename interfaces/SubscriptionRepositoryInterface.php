<?php
declare(strict_types=1);

namespace app\interfaces;

use app\models\Subscription;

/**
 * Интерфейс репозитория подписок
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Возвращает список телефонов для подписчиков указанного автора
     *
     * @param int $authorId
     * @return string[]
     */
    public function getPhonesByAuthorId(int $authorId): array;

    /**
     * Проверяет, существует ли подписка на автора для телефона
     *
     * @param int $authorId
     * @param string $phone
     * @return bool
     */
    public function exists(int $authorId, string $phone): bool;

    /**
     * Сохраняет подписку
     *
     * @param Subscription $subscription
     * @return bool
     */
    public function save(Subscription $subscription): bool;
}
