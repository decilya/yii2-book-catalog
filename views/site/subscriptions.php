<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Subscription[] $subscriptions */

$this->title = 'Все подписки';
?>

<h1><?= Html::encode($this->title) ?></h1>

<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Автор</th>
        <th>Телефон</th>
        <th>Дата подписки</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($subscriptions)): ?>
        <tr><td colspan="4">Подписок пока нет.</td></tr>
    <?php else: ?>
        <?php foreach ($subscriptions as $sub): ?>
            <tr>
                <td><?= $sub->id ?></td>
                <td><?= Html::encode($sub->author->full_name ?? '—') ?></td>
                <td><?= Html::encode($sub->phone) ?></td>
                <td><?= Yii::$app->formatter->asDatetime($sub->created_at) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<div class="text-center mt-3">
    <?= Html::a('Назад на главную', ['site/index'], ['class' => 'btn btn-secondary']) ?>
</div>
