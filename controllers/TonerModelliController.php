<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Modelli;
use app\models\Toner\ModelliSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Toner\ProductModelli;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;
use \yii\web\Response;
use yii\helpers\Html;

/**
 * TonerModelliController implements the CRUD actions for TonerModelli model.
 */
class TonerModelliController extends Controller
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
     * Lists all TonerModelli models.
     * @return mixed
     */
    public function actionIndex($ids = null)
    {
        $searchModel = new ModelliSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//        $dataProvider->sort = false;
//        if ($ids)
//          $dataProvider->query->andWhere(["id" => explode(",", $ids)]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TonerModelli model.
     * @param integer $id
     * @return mixed
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
     * Creates a new TonerModelli model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id_source = null)
    {
        $request = Yii::$app->request;
        $model = new Modelli();

        if($request->isAjax){
          Yii::$app->response->format = Response::FORMAT_JSON;

          if($request->isGet){
            // Valorizzo i valodi di default sulla base del modello sorgente
            if ($id_source)
              $this->defaultFromSource($id_source, $model);
            else {
              $model->readonly["serie"] = false;
              $model->readonly["marca"] = false;
            }

            return [
              'title'=> "Nuovo modello",
              'content'=> $this->renderAjax('create', [
                  'model' => $model,
              ]),
              'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                         Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
            ];
          }else if($model->load($request->post()) && $model->save()){

            if ($id_source) {
              $source = \app\models\Toner\Source\Modelli::findOne($id_source);
              $source->id_modello = $model->id;
              $source->save();
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-modelli-pjax'];
            //return ['forceClose'=>true,'forceReload'=>'true'];
          }else{
            return [
              'title'=> "Nuovo modello",
              'content'=> $this->renderAjax('create', [
                  'model' => $model,
              ]),
              'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                         Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
            ];
          }
        }

    }

    private function defaultFromSource($id_source, &$model) {

      // Recupero il modello sorgente
      $source = \app\models\Toner\Source\Modelli::findOne($id_source);
      // Nome del modello
      $model->nome = $source->nome;
      // Serie del modello
      if (isset($source->sourceSerie->serie)) {
        $model->serie = $source->sourceSerie->serie->nome;
        $model->readonly["serie"] = true;
      }
      else {
        $model->serie = $source->sourceSerie->nome;
      }
      // Marca del modello
      $model->marca = $source->sourceSerie->sourceMarca->marca->nome;
      $model->readonly["marca"] = true;

    }

    /**
     * Updates an existing TonerModelli model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
      $request = Yii::$app->request;
      $model = $this->findModel($id);

      if($request->isAjax){
          /*
          *   Process for ajax request
          */
          Yii::$app->response->format = Response::FORMAT_JSON;
          if($request->isGet){
              return [
                  'title'=> "Modifica modello ".$model->nome,
                  'content'=>$this->renderAjax('update', [
                      'model' => $model,
                  ]),
                  'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                              Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
              ];
          }else if($model->load($request->post()) && $model->save()){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-prodotti-pjax'];
          }else{
               return [
                  'title'=> "Modifica prodotto ".$model->sku,
                  'content'=>$this->renderAjax('update', [
                      'model' => $model,
                  ]),
                  'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                              Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
              ];
          }
      }else{
          /*
          *   Process for non-ajax request
          */
          /*if ($model->load($request->post()) && $model->save()) {
              return $this->redirect(['view', 'id' => $model->id]);
          } else {
              return $this->render('update', [
                  'model' => $model,
              ]);
          }*/
      }
    }

    public function actionFind($q = null) {

      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

      $out = ['results' => ['id' => '', 'text' => '']];

      $q = str_replace(" ", '%', $q);

      if (!is_null($q)) {
          $data = Modelli::find()
            ->andWhere(["like", "concat(marca, ' ', serie, ' ', nome)", $q])
            ->select(["id", "concat(marca, ' ', serie, ' ', nome) as text"])
            ->asArray()
            ->limit(20)
            ->all();
          $out['results'] = array_values($data);
          $out['results'][] = ["id" => 0, "text" => "###NULL###"];
      }

      return $out;
    }

    /**
     * Deletes an existing TonerModelli model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TonerModelli model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TonerModelli the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Modelli::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
