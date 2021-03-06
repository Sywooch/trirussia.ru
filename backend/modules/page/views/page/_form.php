<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model page\models\Page */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="page-form">

    <div class="box-body">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'created')->widget(\kartik\datetime\DateTimePicker::className(), [
                    'name' => 'datetime_10',
                    'options' => ['placeholder' => 'Выберите дату и время'],
                    'convertFormat' => true,
                    'pluginOptions' => [
                        'format' => 'yyyy-MM-dd hh:i',
                        'startDate' => '01-Mar-2014 12:00 AM',
                        'todayHighlight' => true,

                        'weekStart' => '1',
                    ]
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
            </div>
        </div>



        <?= $form->field($model, 'label')->textInput(['maxlength' => true, 'class' => 'form-control w850 ']) ?>

        <?= $form->field($model, 'content')->widget(
            \vova07\imperavi\Widget::className(),
            [
                'settings' => [
                    'lang' => 'ru',
                    'minHeight' => 200,
                    'plugins' => [
                        'clips',
                        'fullscreen'
                    ]
                ]
            ]
        )->label(); ?>



        <?= $form->field($model, 'published')->hiddenInput(['id' => 'published-field'])->label(false); ?>

    </div>
    <div class="box-footer">
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

            <input type="submit" class="btn btn-default toggle-publication" value="<?= $model->published ? 'Снять с публикации' : 'Опубликовать'; ?>">

            <?= $model->isNewRecord ? '' : Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger pull-right',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить этот объект?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
