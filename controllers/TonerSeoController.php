<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\models\Toner\Product;
use app\models\Toner\Modelli;
use \yii\web\Response;
use yii\helpers\Html;

class TonerSeoController extends Controller
{

  public function actionStampapeIndex() {

    $dataProvider = new ActiveDataProvider([
      'query' => \app\models\Toner\Report\SaleCompatibile1y::find(),
      'pagination' => [
        'pageSize' => 50
      ]
    ]);

    return $this->render('stampape', [
        'dataProvider' => $dataProvider,
    ]);

  }

  public function actionStampapeView($id)
  {
    $request = Yii::$app->request;
    $model = $this->findProduct($id);

    if ($model->load($request->post()) && $model->save()) {
        return $this->redirect(['toner-seo/stampape-view', 'id' => $model->id]);
    }

    $query = \app\models\Toner\Adwords::find();
    $query->orWhere(["like", "keyword", str_replace("-", "%", $model->sku)]);
    $skus = explode("#", $model->part_number);
    foreach ($skus as $sku) {
      if (!$sku)
        continue;
      $query->orWhere(["like", "keyword", str_replace("-", "%", $sku)]);
    }

    $dataProvider1 = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 50
      ],
      'sort' => false
    ]);

    $query = \app\models\Toner\Modelli::find();
    $query->joinWith("products")
      ->andWhere(["id_prodotto" => $id]);
    $dataProvider2 = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 50
      ]
    ]);

    return $this->render('stampape-view', [
        'model' => $model,
        'dataProvider1' => $dataProvider1,
        'dataProvider2' => $dataProvider2,
    ]);

  }

  public function actionStampapeUpdate($id)
  {
    $request = Yii::$app->request;
    $model = $this->findModel($id);

    if($request->isAjax){
        /*
        *   Process for ajax request
        */
        Yii::$app->response->format = Response::FORMAT_JSON;
        if($request->isGet){
            return $this->returnForm($model);
        }else if($model->load($request->post()) && $model->save()){
          Yii::$app->response->format = Response::FORMAT_JSON;
          return ['forceClose'=>true,'forceReload'=>'#crud-datatable-prodotti-pjax'];
        }else{
          return $this->returnForm($model);
        }
    }
  }

  private function returnForm($model) {

    return [
       'title'=> "Modifica modello {$model->marca} {$model->serie} {$model->nome}",
       'content'=>$this->renderAjax('stampape-update', [
           'model' => $model,
       ]),
       'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                   Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
   ];

  }

  protected function findModel($id)
  {
      if (($model = Modelli::findOne($id)) !== null) {
          return $model;
      } else {
          throw new NotFoundHttpException('The requested page does not exist.');
      }
  }

  protected function findProduct($id)
  {
      if (($model = Product::findOne($id)) !== null) {
          return $model;
      } else {
          throw new NotFoundHttpException('The requested page does not exist.');
      }
  }

}

?>
