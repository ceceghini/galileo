<?php

namespace app\controllers;

use Yii;
use app\models\Toner\ProductSale;
use app\models\Toner\ProductSaleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProductSaleController implements the CRUD actions for ProductSale model.
 */
class ProductSaleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ProductSale models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSaleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductSale model.
     * @param integer $id_product
     * @param string $tipologia
     * @param string $period
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id_product, $tipologia, $period)
    {
        return $this->render('view', [
            'model' => $this->findModel($id_product, $tipologia, $period),
        ]);
    }

    /**
     * Creates a new ProductSale model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductSale();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id_product' => $model->id_product, 'tipologia' => $model->tipologia, 'period' => $model->period]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductSale model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id_product
     * @param string $tipologia
     * @param string $period
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id_product, $tipologia, $period)
    {
        $model = $this->findModel($id_product, $tipologia, $period);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id_product' => $model->id_product, 'tipologia' => $model->tipologia, 'period' => $model->period]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductSale model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id_product
     * @param string $tipologia
     * @param string $period
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id_product, $tipologia, $period)
    {
        $this->findModel($id_product, $tipologia, $period)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ProductSale model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id_product
     * @param string $tipologia
     * @param string $period
     * @return ProductSale the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id_product, $tipologia, $period)
    {
        if (($model = ProductSale::findOne(['id_product' => $id_product, 'tipologia' => $tipologia, 'period' => $period])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
