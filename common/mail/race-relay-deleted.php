<?php
use yii\helpers\Html;

echo Html::tag('p', $user_name . ', к сожалению, вы не сможете попасть в команду к ' . $first_user_name . ' на этап ' . $sport . '. Но не расстраивайтесь, вы можете собрать свою эстафетную команду. Для этого перейдите на страницу соревнования (' . Html::a($race_label, $race_url) . ') и выберите «Хочу в эстафету». Затем выберите этап и укажите приблизительное время, за которое вы можете пройти этап.');
echo Html::tag('p', 'После этого вы станете капитаном команды и сможете собрать свою команду мечты!');
echo Html::tag('p', '');
echo Html::tag('p', 'С уважением,');
echo Html::tag('p', 'Команда TriRussia.ru');
