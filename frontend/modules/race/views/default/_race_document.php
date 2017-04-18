<?php
use yii\helpers\Html;
use metalguardian\fileProcessor\helpers\FPM;

if ($model->file->extension === 'pdf') {
    $src = '/img/download_file.png';
    $img_class = 'img-fluid m-b-1 not-fancy';
}else {
    $src = FPM::originalSrc($model->fpm_file_id);
    $img_class = 'img-fluid m-b-1';
}

echo Html::tag('div',
    Html::img($src, ['class' => $img_class]) .
    Html::tag('p', Html::a($model->file->base_name, FPM::originalSrc($model->fpm_file_id), ['class' => 'underline', 'target' => '_blank']),['class' => 'small text-xs-center']),
    ['class' => 'col-xl-4']);