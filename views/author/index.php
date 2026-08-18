<?php
use yii\helpers\Html;
$this->title = 'Авторы';
?>
<h1><?= Html::encode($this->title) ?></h1>
<p><?= Html::a('Добавить автора', ['create'], ['class' => 'btn btn-success']) ?></p>
<table class="table">
    <thead><tr><th>ФИО</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($authors as $author): ?>
        <tr>
            <td><?= Html::a(Html::encode($author->full_name), ['view', 'id' => $author->id]) ?></td>
            <td>
                <?= Html::a('Редактировать', ['update', 'id' => $author->id], ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $author->id], [
                    'class' => 'btn btn-sm btn-danger',
                    'data-confirm' => 'Удалить автора?',
                    'data-method' => 'post',
                ]) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
