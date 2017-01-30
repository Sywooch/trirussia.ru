<?php
namespace frontend\controllers;

use distance\models\DistanceCategory;
use organizer\models\Organizer;
use promo\models\Promo;
use race\models\Race;
use race\models\RaceDistanceCategoryRef;
use seo\models\Seo;
use sport\models\Sport;
use user\models\User;
use willGo\models\WillGo;
use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\authclient\ClientInterface;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                /*'only' => ['logout', 'signup'],*/
                'rules' => [
                    [
                        'actions' => [
                            'error',
                            'auth',
                            'index',
                            'magazine',
                            'about',
                            'advertising',
                            'domains',
                            'bmi',
                            'convert',
                            'search-races',
                            'sport',
                        ],
                        'allow'   => true,
                    ],
                    [
                        'actions' => [ 'logout', 'calendar'],
                        'allow'   => true,
                        'roles'   => [ '@' ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post', 'get'],
                ],
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'oAuthSuccess'],
            ],
        ];
    }

    /**
     * This function will be triggered when user is successfuly authenticated using some oAuth client.
     *
     * @param ClientInterface $client
     * @return bool Response
     */
    public function oAuthSuccess($client) {
        if (!$this->action instanceof \yii\authclient\AuthAction) {
            throw new \yii\base\InvalidCallException("successCallback is only meant to be executed by AuthAction!");
        }

        $attributes = $client->getUserAttributes();
        Yii::info(json_encode($attributes));
        //$client->setReturnUrl(\Yii::$app->request->url);

        if (!empty($attributes['email'])){
            $user = User::find()->where(['email'=>$attributes['email']])->one();
            if (!$user){
                $user = new User();
                $user->email = $attributes['email'];
                $user->username = $attributes['email'];
                $user->fb_id = $attributes['id'];
                $user->first_name = explode(' ', $attributes['name'])[0];
                $user->last_name = explode(' ', $attributes['name'])[1];
                $user->sex = $attributes['gender'];
                $user->locale = $attributes['locale'];
                $user->timezone = $attributes['timezone'];
                $user->age = $attributes['age_range'];
                $user->birthday = $attributes['birthday'];
                $user->place = $attributes['location']['name'];
                $user->save(false);
            }
            Yii::$app->user->login($user);
        }
    }


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $promos = Promo::find()->orderBy('created DESC')->limit(6)->all();

        $sportModel = null;

        $mainRaces = Race::find()->where(['>=', 'start_date', date('Y-m-d', time())])->published()->orderBy('start_date ASC, id DESC')->limit(12)->all();
        $secondaryRaces = Race::find()->where(['>=', 'start_date', date('Y-m-d', time())])->published()->orderBy('start_date ASC, id DESC')->limit(12)->offset(12)->all();
        $lastRaces = Race::find()->where(['>=', 'start_date', date('Y-m-d', time())])->published()->orderBy('start_date ASC, id DESC')->limit(13)->offset(24)->all();

        $showMore = false;
        if (count($lastRaces) > 12){
            $showMore = true;
            array_pop($lastRaces);
        }

        return $this->render('index', [
            'mainRaces' => $mainRaces,
            'promos' => $promos,
            'showMore' => $showMore,
            'secondaryRaces' => $secondaryRaces,
            'lastRaces' => $lastRaces,
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionSport($sport)
    {
        $races = Race::searchForSportPage($sport);

        Seo::registerModel(Sport::getCurrentSportModel());

        $showMore = false;
        if (count($races) > 12){
            $showMore = true;
            array_pop($races);
        }



        return $this->render('races', [
            'races' => $races,
            'showMore' => $showMore,
        ]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionSearchRaces()
    {
        $sportModelSeo = Sport::getCurrentSportModel();
        if (!$sportModelSeo){
            $sportModelSeo = new Sport();
        }

        Seo::registerModel($sportModelSeo);

        $raceCondition = Race::find();

        $sportModel = null;
        if (!empty($_GET['sport'])){
            if ($sportModel = Sport::find()->where(['url' => $_GET['sport']])->one()) {
                $raceCondition->andWhere(['sport_id'  => $sportModel->id ]);
            } else {
                throw new NotFoundHttpException();
            }
        }

        if (!empty($_GET['distance'])){
            $idArray = [];
            $distance = DistanceCategory::find()
                ->where(['label' => $_GET['distance'], ]);
            if ($sportModel){
                $distance->andWhere(['sport_id'  => $sportModel->id ]);
            }
            $distance = $distance->one();
            if (!$distance){
                throw new NotFoundHttpException();
            }
            $refs = RaceDistanceCategoryRef::find()->where(['distance_category_id' => $distance->id, ])->all();
            foreach ($refs as  $ref)
                $idArray[] = $ref->race_id;
            if (!empty($idArray))
                $raceCondition->andWhere(['in', Race::tableName() . '.id', $idArray]);
        }

        if (!empty($_GET['date'])){
            $dateFrom = $_GET['date'] . '-01';
            if (substr($dateFrom, 0, 8) == date('Y-m-')){
                $dateFrom = substr($_GET['date'], 0, 8).date('-d');
            }
            $raceCondition->andWhere(['between', 'start_date', $dateFrom, substr($_GET['date'], 0, 8) . '-31']);
        } else {
            $raceCondition->andWhere(['>=', 'start_date', date('Y-m-d', time())]);
        }
        if (!empty($_GET['country'])) $raceCondition->andWhere([Race::tableName().'.country' => $_GET['country']]);

        if (!empty($_GET['organizer'])){
            $raceCondition->leftJoin(Organizer::tableName(), 'organizer_id = organizer.id');
            $raceCondition->andWhere(['organizer.label' => $_GET['organizer']]);
        }

        if (!empty($_GET['sort']) && $_GET['sort'] == 'popular'){
            $races = $raceCondition->orderBy('popularity DESC, start_date ASC, id DESC')->limit(13)->all();
        } else {
            $races = $raceCondition->orderBy('start_date ASC, id DESC')->limit(13)->all();
        }
        $showMore = false;
        if (count($races) > 12){
            $showMore = true;
            array_pop($races);
        }
        return $this->render('search-races', [
            'races' => $races,
            'showMore' => $showMore,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionCalendar(){
        $joins = ArrayHelper::map(WillGo::find()->where(['user_id' => Yii::$app->user->identity->id])->all(), 'race_id', 'race_id');
        $idArray = array_values($joins);

        $races = Race::find()->where(['in', 'id', $idArray])->all();
        $racesArrayImproved = [];
        /** @var Race $race */
        foreach ($races as $race){
            if (!isset($racesArrayImproved[strtotime($race->start_date)])){
                $racesArrayImproved[strtotime($race->start_date)] = [$race];
            } else {
                $racesArrayImproved[strtotime($race->start_date)][] = $race;
            }

        }

        $notJoinedRaces = Race::find()
            ->where(['not in', 'id', $idArray])
            ->andWhere(['between', 'start_date', date('Y-m-d'), date('Y-m-d', time()+92*24*60*60)])
            ->all();
        $notJoinedRacesArrayImproved = [];
        /** @var Race $race */
        foreach ($notJoinedRaces as $race){
            if (!isset($notJoinedRacesArrayImproved[strtotime($race->start_date)])){
                $notJoinedRacesArrayImproved[strtotime($race->start_date)] = [$race];
            } else {
                $notJoinedRacesArrayImproved[strtotime($race->start_date)][] = $race;
            }

        }

        return $this->render('calendar', ['races'=>$racesArrayImproved, 'notJoinedRaces'=>$notJoinedRacesArrayImproved,]);
    }

    
    //static pages

    public function actionAdvertising()
    {
        return $this->render('advertising');
    }

    public function actionDomains()
    {
        return $this->render('domains');
    }

    public function actionBmi()
    {
        return $this->render('bmi');
    }

    public function actionConvert()
    {
        return $this->render('convert');
    }
}
