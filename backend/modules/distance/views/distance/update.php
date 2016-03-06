<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model distance\models\Distance */

$this->title = 'Update Distance: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Distances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="distance-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
