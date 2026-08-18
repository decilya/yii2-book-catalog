<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
?>
<h2>Подписка на новые книги автора</h2>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="form-group">
        <label>Выберите автора</label>
        <?= Html::dropDownList('author_id', null, ArrayHelper::map($authors, 'id', 'full_name'), ['class' => 'form-control', 'prompt' => '-- выберите --', 'required' => true]) ?>
    </div>
    <div class="form-group">
        <label>Ваш номер телефона</label>
        <input type="text" name="phone" class="form-control" placeholder="+79119947871" required>
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Подписаться</button>
</form>
