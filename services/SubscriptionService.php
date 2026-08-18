<?php
declare(strict_types=1);

namespace app\services;

use app\interfaces\SubscriptionRepositoryInterface;
use app\interfaces\AuthorRepositoryInterface;
use app\models\Subscription;

/**
 * Сервис для управления подписками
 */
class SubscriptionService
{
    private SubscriptionRepositoryInterface $subRepo;
    private AuthorRepositoryInterface $authorRepo;

    public function __construct(
        SubscriptionRepositoryInterface $subRepo,
        AuthorRepositoryInterface $authorRepo
    ) {
        $this->subRepo = $subRepo;
        $this->authorRepo = $authorRepo;
    }

    /**
     * Подписывает пользователя на уведомления о новых книгах автора
     *
     * @param int $authorId
     * @param string $phone
     * @return bool
     */
    public function subscribe(int $authorId, string $phone): bool
    {
        if (!$this->authorRepo->findById($authorId)) {
            return false;
        }
        if ($this->subRepo->exists($authorId, $phone)) {
            return true; // уже подписан
        }

        $subscription = new Subscription();
        $subscription->author_id = $authorId;
        $subscription->phone = $phone;
        return $this->subRepo->save($subscription);
    }
}
