<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Source\Serie;
use app\models\Toner\Source\SerieSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

/**
 * TonerSourceSerieController implements the CRUD actions for Serie model.
 */
class TonerSourceSerieController extends Controller
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

    public function actions() {

      return ArrayHelper::merge(parent::actions(), [
        'set-serie' => [
          'class' => EditableColumnAction::className(),
          'modelClass' => Serie::className(),                // the update model class
          'outputValue' => function ($model, $attribute, $key, $index) {
            if ($attribute == "id_serie")
              return \app\models\Toner\Serie::findOne($model->$attribute)->nome;
            else
              return $model->$attribute;
          },
          'outputMessage' => function($model, $attribute, $key, $index) {
            return '';                                  // any custom error after model save
          },
        ]
      ]);

    }

    /**
     * Lists all Serie models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SerieSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Serie model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionView($id)
    {
      $model = $this->findModel($id);

      $dataProviderModelli = new ArrayDataProvider([
        'allModels' => $model->modelli,
        'sort' => false
      ]);

      return $this->render('view', [
          'model' => $model,
          'dataProviderModelli' => $dataProviderModelli
      ]);
    }*/

    /**
     * Creates a new Serie model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {

      $model = $this->findModel($id);

      $serie = new \app\models\Toner\Serie();
      $serie->id_marca = $model->sourceMarca->id_marca;
      $serie->nome = $model->nome;
      $serie->save();

      $model->id_serie = $serie->id;
      $model->save();

      return $this->redirect($_SERVER["HTTP_REFERER"]);

    }

    /**
     * Updates an existing Serie model.
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
     * Deletes an existing Serie model.
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
     * Finds the Serie model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Serie the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Serie::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
