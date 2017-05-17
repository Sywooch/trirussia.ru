<?php
use yii\helpers\Html;
?>

<div class="container">
    <div class="pull-left">
        <h1 class="m-y-3">Кэмпы по триатлону, велоспорту и бегу</h1>
    </div>
    <div class="clearfix"></div>

    <div class="row camps-block">
        <?php
        foreach ($models as $model) {
            echo $this->render('card', [
                'model' => $model,
            ]);
        }
        ?>
    </div>
    <div class="block block-more-races block-more-races-sport ">
        <button type="submit" data-lock="0" data-url="/camp/default/get-more-camps" data-target=".camps-block"  data-render-type="search" data-sort="" class="btn btn-primary more-races" >
            Загрузить еще кэмпы
        </button>
    </div>
</div>