<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Книги';
?>
<h1><?= Html::encode($this->title) ?></h1>
<p><?= Html::a('Добавить книгу', ['create'], ['class' => 'btn btn-success']) ?></p>
<table class="table table-striped">
    <thead>
    <tr><th>Название</th><th>Год</th><th>Авторы</th><th>Действия</th></tr>
    </thead>
    <tbody>
    <?php foreach ($books as $book): ?>
        <tr>
            <td><?= Html::a(Html::encode($book->title), ['view', 'id' => $book->id]) ?></td>
            <td><?= $book->year ?></td>
            <td>
                <?php
                $names = array_map(fn($a) => Html::encode($a->full_name), $book->authors);
                echo implode(', ', $names);
                ?>
            </td>
            <td>
                <?= Html::a('Редактировать', ['update', 'id' => $book->id], ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $book->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'data-confirm' => 'Удалить книгу?',
                    'data-method' => 'post',
                ]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
