<?php
use yii\helpers\Html;
$this->title = $author->full_name;
?>
<h1><?= Html::encode($author->full_name) ?></h1>
<p><?= Html::a('Редактировать', ['update', 'id' => $author->id], ['class' => 'btn btn-primary']) ?></p>
