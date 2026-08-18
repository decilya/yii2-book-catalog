<?php
declare(strict_types=1);

namespace app\repositories;

use app\interfaces\SubscriptionRepositoryInterface;
use app\models\Subscription;

/**
 * Реализация репозитория подписок через ActiveRecord
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getPhonesByAuthorId(int $authorId): array
    {
        return Subscription::find()
            ->where(['author_id' => $authorId])
            ->select('phone')
            ->column();
    }

    /**
     * @inheritDoc
     */
    public function exists(int $authorId, string $phone): bool
    {
        return Subscription::find()
            ->where(['author_id' => $authorId, 'phone' => $phone])
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function save(Subscription $subscription): bool
    {
        return $subscription->save();
    }
}
