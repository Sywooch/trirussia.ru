<?php
use yii\helpers\Url;
use metalguardian\fileProcessor\helpers\FPM;
/**
 * @var $races []
 * @var $sport \sport\models\Sport
 */
?>

<h4 class="PTSerif"><i>Самые ожидаемые соревнования <?= $sport ? 'по ' . \sport\models\Sport::getCurrentSportLabel('дательный') : '';?></i></h4>
<div class="row">
    <?php /** @var $race \race\models\Race */?>
   <?php foreach ($races as $race) {?>
       <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 m-t-1">
           <a href="<?= $race->getViewUrl(); ?>">
               <div class="embed-responsive embed-responsive-16by9">
                   <img alt="<?= $race->label; ?>" class="embed-responsive-item lazy" data-original="<?= FPM::originalSrc($race->main_image_id); ?>">
               </div>
           </a>
           <h5 class="m-t-1">
               <a href="<?= $race->getViewUrl(); ?>" class="underline-black">
                   <?= $race->label; ?>
               </a>
           </h5>
           <p>
               <?= $race->promo; ?>
           </p>
           <span class="text-muted small"><?= $race->getDateRepresentation(); ?>,
                   <?= $race->region; ?>
           </span>
       </div>
    <?php } ?>
</div>