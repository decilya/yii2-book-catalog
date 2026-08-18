<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'year')->textInput() ?>
<?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>
<?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'photo')->fileInput() ?>
<?= $form->field($model, 'author_ids')->checkboxList(
    ArrayHelper::map($authors, 'id', 'full_name')
) ?>
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
<?php ActiveForm::end(); ?>
