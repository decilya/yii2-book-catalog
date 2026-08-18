<?php
use yii\helpers\Html;
$this->title = $book->title;
?>
<h1><?= Html::encode($book->title) ?></h1>
<p><strong>Год:</strong> <?= $book->year ?></p>
<p><strong>ISBN:</strong> <?= Html::encode($book->isbn) ?></p>
<p><strong>Описание:</strong> <?= Html::encode($book->description) ?></p>
<p><strong>Авторы:</strong>
    <?php
    $names = array_map(fn($a) => Html::encode($a->full_name), $book->authors);
    echo implode(', ', $names);
    ?>
</p>
<?php if ($book->photo): ?>
    <p><img src="<?= $book->photo ?>" alt="Обложка" style="max-width:200px;"></p>
<?php endif; ?>
<p><?= Html::a('Редактировать', ['update', 'id' => $book->id], ['class' => 'btn btn-primary']) ?></p>
