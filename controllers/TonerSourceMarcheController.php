<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Source\Marche;
use app\models\Toner\Source\MarcheSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * TonerMarcheSourceController implements the CRUD actions for Marche model.
 */
class TonerSourceMarcheController extends Controller
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
     * Lists all Marche models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MarcheSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Marche model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionView($id)
    {
      $model = $this->findModel($id);

      $dataProviderSerie = new ActiveDataProvider([
        'query' => \app\models\Toner\Source\Serie::find()->andWhere(["id_source_marca" => $id]),
        'sort' => false
      ]);

      return $this->render('view', [
          'model' => $model,
          'dataProviderSerie' => $dataProviderSerie,
      ]);
    }*/

    /**
     * Creates a new Marche model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*public function actionCreate()
    {
        $model = new Marche();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }*/

    /**
     * Updates an existing Marche model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }*/

    /**
     * Deletes an existing Marche model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }*/

    /**
     * Finds the Marche model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Marche the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Marche::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
