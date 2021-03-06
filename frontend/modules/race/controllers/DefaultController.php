<?php

namespace race\controllers;

use common\components\CountryList;
use common\models\UserInfo;
use race\models\RaceRating;
use race\models\RaceRegistration;
use Yii;
use distance\models\Distance;
use distance\models\DistanceCategory;
use distance\models\DistanceDistanceCategoryRef;
use frontend\models\SearchRaceForm;
use frontend\widgets\searchRacesPanel\SearchRacesPanel;
use organizer\models\Organizer;
use race\models\Race;
use race\models\RaceDistanceCategoryRef;
use seo\models\Seo;
use sport\models\Sport;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `race` module
 */
class DefaultController extends Controller
{
    public function actionView($url){
        /** @var Race $race */
        $race = Race::find()->where(['url' => $url])->forUser()->one();
        if (!$race){
            throw new NotFoundHttpException();
        }
        Seo::registerModel($race);
        $race->addStatisticsView();

        $viewName = 'view';
        if ($race->start_date < date('Y-m-d', time())) {
            $viewName = 'past';
        }

        return $this->render($viewName, ['race' => $race, ]);
    }
    
    public function actionUpdateSearchDistance(){
        if (\Yii::$app->request->isAjax){
            $distanceCategories = DistanceCategory::find()
                ->where( !empty($_POST['sportId']) ? ['sport_id' => $_POST['sportId']] : [] )
                ->all();
            return $this->renderAjax('_options', ['options' => ArrayHelper::map($distanceCategories, 'id', 'label'), ]);
        } 
        throw new NotFoundHttpException();
    }
    
    public function actionUpdateUrl(){
        if (\Yii::$app->request->isAjax){
            if ($_POST['SearchRaceForm']){
                $data = [];
                foreach ($_POST['SearchRaceForm'] as $key => $value){
                    if ($value && $key != 'sport'){
                        $data[$key] = $value;
                    }
                }

                $sport = '';
                if (!empty($_POST['SearchRaceForm']['sport'])) {
                    $sport = Sport::findOne($_POST['SearchRaceForm']['sport']);
                    if (!$sport)
                        throw new NotFoundHttpException();
                    $sport = $sport->url;
                }

                if ($sport)
                    $url = '/' . $sport;
                else
                    $url = '/search-races';
                if (!empty($data))
                    $url .= '?' . http_build_query($data);
                return $this->renderAjax('_submit-url', ['url' => $url, ]);
            }
        }
        throw new NotFoundHttpException();
    }

    public function actionGetMoreRaces()
    {
        $this->layout = false;
        $limit = Yii::$app->request->post('limit') ? Yii::$app->request->post('limit') : 12;

        $page = $_POST['page'] + 2;
        if (!empty($_POST['renderType']) && $_POST['renderType'] == 'search' )
            $page = $_POST['page'];

        $raceCondition = Race::find();

        $sport = null;
        if (!empty($_POST['sport'])){
            $sport = $_POST['sport'];
        }

        if ($sport){
            $page = $_POST['page'];
            if ($sportModel = Sport::find()->where(['url' => $sport])->one()) {
                $raceCondition->andWhere(['sport_id'  => $sportModel->id ]);
            }
        }


        if (!empty($_POST['distance'])){
            $idArray = [];
            $distance = DistanceCategory::find()->where(['label' => $_POST['distance'], 'sport_id'  => $sport->id ])->one();
            if (!$distance){
                throw new NotFoundHttpException();
            }
            $refs = RaceDistanceCategoryRef::find()->where(['distance_category_id' => $distance->id, ])->all();
            foreach ($refs as  $ref)
                $idArray[] = $ref->race_id;
            if (!empty($idArray))
                $raceCondition->andWhere(['in', Race::tableName().'.id', $idArray]);
        }

        if (!empty($_POST['date'])){
            $raceCondition->andWhere(['between', 'start_date', $_POST['date'], substr($_POST['date'], 0, 8) . '31']);
        } else {
            $raceCondition->andWhere(['>=', 'start_date', date('Y-m-d', time())]);
        }

        if (!empty($_POST['country'])) $raceCondition->andWhere(['country' => $_POST['country']]);

        if (!empty($_POST['organizer'])){
            $raceCondition->leftJoin(Organizer::tableName(), 'organizer_id = organizer.id');
            $raceCondition->andWhere([Organizer::tableName().'.label' => $_POST['organizer']]);
        }

        $raceCondition->andWhere(['race.published' => 1]);

        if (!empty($_POST['sort']) && $_POST['sort'] == 'popular'){
            $races = $raceCondition->orderBy('popularity DESC, start_date ASC, id DESC')->limit($limit)->offset($page*$limit)->all();
        } else {
            $races = $raceCondition->orderBy('start_date ASC, id DESC')->limit($limit)->offset($page*$limit)->all();
        }



        if (!empty($_POST['renderType']) && $_POST['renderType'] == 'search' )
            return Json::encode([
                'result' => count($races),
                'data' => $this->render('_more-races-search', ['moreRaces' => $races]),
                'list' => $this->render('_more-races-search-list', ['moreRaces' => $races]),
            ]);

        return Json::encode([
            'result' => count($races),
            'data' => $this->render('_more-races', ['moreRaces' => $races]),
        ]);
    }

    public function actionCreate() {
        $model = new Race();
        $model->published = 0;
        $model->country = 'Россия';
        $countryList = (new CountryList())->getCountryDropDown();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->set('created_race', $model->id);
            return $this->redirect(['advanced']);
        } else {
            $model->sport_id = null;
            $model->currency = 'рубли';
            $sport = Sport::find()->one();
            $distanceList = $this->renderDistanceList($sport->id);
            return $this->render('create', [
                'model' => $model,
                'distanceList' => $distanceList,
                'countryList' => $countryList,
            ]);
        }
    }

    public function actionRenderDistanceList() {
        $id = (int) Yii::$app->request->post('id');

        return $this->renderDistanceList($id);
    }

    public function actionAdvanced() {
        $yandexMoney = '41001650080726';
        $id = Yii::$app->session->get('created_race');
        if (!$id) {
            return $this->redirect(['create']);
        }
        Yii::$app->session->remove('created_race');

        $race = Race::find()->where(['id' => $id])->one();

        return $this->render('advanced', [
            'yandexMoney' => $yandexMoney,
            'race' => $race,
        ]);
    }

    public function actionSetRating() {
        $race_id = (int) Yii::$app->request->post('race');
        $rate = (int) Yii::$app->request->post('rate');

        $race = Race::find()->where(['id' => $race_id])->one();
        if ($rate < 1 || $rate > 5 || Yii::$app->user->isGuest || !$race) {
            return true;
        }

        $raceRaiting = RaceRating::find()->where(['user_id' => Yii::$app->user->identity->id, 'race_id' => $race->id])->one();
        if (!$raceRaiting) {
            $raceRaiting = new RaceRating();
            $raceRaiting->user_id = Yii::$app->user->identity->id;
            $raceRaiting->race_id = $race->id;
        }
        $raceRaiting->rate = $rate;
        $raceRaiting->save();
        return number_format(round($race->rating, 2), 2, '.', '');
    }

    public function renderDistanceList($id) {
        $distanceCategory = DistanceCategory::find()->where(['sport_id' => $id])->all();
        $distanceDistanceCategoryRef = DistanceDistanceCategoryRef::find()->where(['distance_category_id' => ArrayHelper::getColumn($distanceCategory, 'id')])->all();
        $distances = Distance::find()->where(['id' => ArrayHelper::getColumn($distanceDistanceCategoryRef, 'distance_id')])->all();
        ArrayHelper::multisort($distances, 'label', SORT_ASC, SORT_NATURAL);

        $distancesArray = ArrayHelper::map($distances, 'id', 'label');
        $categoriesArray = ArrayHelper::getColumn($distanceCategory, 'id');

        $model = new Race();

        return $this->renderPartial('_distances-list', [
            'model' => $model,
            'distancesArray' => $distancesArray,
            'categoriesArray' => $categoriesArray,
        ]);
    }

    public function actionRegister() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $race_id = Yii::$app->request->post('race_id');
        $distance_id = Yii::$app->request->post('distance_id');
        $type =  Yii::$app->request->post('type');

        $userInfo = UserInfo::find()->where(['user_id' => Yii::$app->user->id])->one();
        if (!$userInfo) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'register-to-race',
                'value' => json_encode([
                    'race_id' => $race_id,
                    'distance_id' => $distance_id,
                    'type' => $type,
                ]),
            ]));
            return ['redirect' => Url::to(['/user/default/about'])];
        }

        $race = Race::find()->where(['id' => $race_id])->forUser()->one();
        if ($race) {
            $race->registerUser($distance_id, $type);
        }

        return true;

    }

    public function actionSearch($q) {
        if ($q) {
            $races = Race::find()->where(['like', 'label', $q])->andWhere(['>=', 'start_date', date('Y-m-d', time())])->forUser()->all();
        }else {
            $races = [];
        }
        return $this->render('search', [
            'q' => $q,
            'races' => $races
        ]);
    }
}
