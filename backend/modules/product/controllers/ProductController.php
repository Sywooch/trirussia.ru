<?php

namespace product\controllers;

use backend\components\BackController;
use product\models\ProductAttr;
use product\models\ProductImage;
use product\models\ProductProductAttrValue;
use Yii;
use product\models\Product;
use product\models\ProductSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends BackController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [

                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Product();
        $attrs = [];
        $checkedAttr = [];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'attrs' => $attrs,
                'checkedAttr' => $checkedAttr,
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $attrs = ProductAttr::find()->where(['category_id' => $model->category_id])->all();
        $checkedAttr = ArrayHelper::getColumn(ProductProductAttrValue::find()->where(['product_id' => $model->id])->all(), 'value_id');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'attrs' => $attrs,
                'checkedAttr' => $checkedAttr,
            ]);
        }
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Ищет атрибуты для выбранной категории
     */
    public function actionGetattrlist() {
        $id = Yii::$app->request->post('id');
        $attrs = ProductAttr::find()->where(['category_id' => $id])->all();
        $checkedAttr = [];

        return $this->renderPartial('_attr', [
            'attrs' => $attrs,
            'checkedAttr' => $checkedAttr,
        ]);
    }

    public function actionDeleteimg() {
        $id = Yii::$app->request->post('id');
        $image = ProductImage::find()->where(['id' => $id])->one();
        $image->delete();
    }
}
