<?php
use yii\helpers\Html;
$this->title = 'Редактировать автора: ' . $model->full_name;
?>
    <h1><?= Html::encode($this->title) ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
