<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Source\Modelli;
use app\models\Toner\Source\ModelliSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use \yii\web\Response;

/**
 * TonerSourceModelliController implements the CRUD actions for Modelli model.
 */
class TonerSourceModelliController extends Controller
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
                    'bulk-delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actions() {

      return ArrayHelper::merge(parent::actions(), [
        'editable-update' => [
          'class' => EditableColumnAction::className(),
          'modelClass' => Modelli::className(),                // the update model class
          'outputValue' => function ($model, $attribute, $key, $index) {
            return null;
          },
          'outputMessage' => function($model, $attribute, $key, $index) {
            return '';                                  // any custom error after model save
          },
        ]
      ]);

    }

    public function actionJoin($id) {

      $model = $this->findModel($id);
      $model->id_modello = null;
      $model->save();

      return $this->redirect($_SERVER["HTTP_REFERER"]);

    }

    /**
     * Lists all Modelli models.
     * @return mixed
     */
    public function actionIndex($filter=null)
    {
        $searchModel = new ModelliSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($filter=="noncollegati") {

          $dataProvider->query
          ->andWhere(["id_modello" =>null])
          ->andWhere("exists (select 1
                                from toner_source_product p
                                  join toner_source_product_modelli pm on p.id = pm.id_product
                               where not exists (select 1 from toner_source_product_join pj where p.id = pj.id_source_product)
                                 and p.disabled = 0
                                 and pm.id_modello = toner_source_modelli.id)");

        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'filter' => $filter
        ]);
    }

    /**
     * Displays a single Modelli model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $model = $this->findModel($id);

        $dataProviderProduct = new ArrayDataProvider([
          'allModels' => $model->products,
          'sort' => false
        ]);

        return $this->render('view', [
            'model' => $model,
            'dataProviderProduct' => $dataProviderProduct
        ]);
    }

    /**
     * Updates an existing Modelli model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
      $request = Yii::$app->request;
      $this->findModel($id)->delete();

      if($request->isAjax){
          /*
          *   Process for ajax request
          */
          Yii::$app->response->format = Response::FORMAT_JSON;
          return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
      }else{
          /*
          *   Process for non-ajax request
          */
          return $this->redirect(['index']);
      }
    }

    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
        foreach ( $pks as $pk ) {
            $model = $this->findModel($pk);
            $model->delete();
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }

    }

    /**
     * Finds the Modelli model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Modelli the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Modelli::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
