<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Source\Product;
use app\models\Toner\Source\ProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use \yii\web\Response;

/**
 * TonerSourceProductController implements the CRUD actions for Product model.
 */
class TonerSourceProductController extends Controller
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

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex($filter=null)
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = false;
        $dataProvider->pagination->pageSize=100;

        if ($filter=="noncollegati") {
          $dataProvider->query
            ->andWhere(["disabled" => 0])
            ->andWhere("not exists (select 1 from toner_source_product_join where toner_source_product.id = toner_source_product_join.id_source_product)");
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'filter' => $filter
        ]);
    }

    public function actionCreateModelli($id) {

      $model = $this->findModel($id);
      $source_modelli = $model->getModelli()->all();

      foreach ($source_modelli as $source) {

        if ($source->id_modello)
          continue;

        $modello = new \app\models\Toner\Modelli();
        $modello->nome = $source->nome;
        if (isset($source->sourceSerie->serie)) {
          $modello->serie = $source->sourceSerie->serie->nome;
        }
        else {
          $modello->serie = $source->sourceSerie->nome;
        }
        // Marca del modello
        $modello->marca = $source->sourceSerie->sourceMarca->marca->nome;

        $modello->save();

        $source->id_modello = $modello->id;
        $source->save();

      }

      return $this->redirect(['view', 'id' => $id]);

    }

    public function actionJoin($id, $id_product) {

      $model = $this->findModel($id);
      $model->join($id_product);

      return $this->redirect(['view', 'id' => $id]);

    }

    public function actionUnjoin($id, $id_product) {

      $model = $this->findModel($id);
      $model->unJoin($id_product);

      return $this->redirect(['view', 'id' => $id]);

    }



    public function actionEnableDisable($id) {

      $model = $this->findModel($id);
      if ($model->disabled)
        $model->disabled = 0;
      else
        $model->disabled = 1;

      $model->save();

      return $this->redirect($_SERVER["HTTP_REFERER"]);

    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $model = $this->findModel($id);

        // Modelli collegati
        $dataProviderModelli = new ActiveDataProvider([
          'query' => $model->getModelli(),
          'sort' => false
        ]);

        $searchModelProdotti = new \app\models\Toner\ProductSearch();
        $dataProviderProdotti = $searchModelProdotti->search(Yii::$app->request->queryParams);
        $dataProviderProdotti->sort = false;
        $dataProviderProdotti->pagination->pageSize=10;

        if (!isset(Yii::$app->request->queryParams["ProductSearch"]))
          $productSearch = array();
        else
          $productSearch = array_filter(Yii::$app->request->queryParams["ProductSearch"]);

        if (empty($productSearch)) {
          $dataProviderProdotti->query->joinWith("sourceProductsJoin")
            ->andWhere(["id_source_product" => $id]);
        }

        $create_ok = 99;

        if ($model->source == "tuttocartucce") {

          $sku = str_replace("-", "", $model->sku);

          $queryProdotti = Product::find()
            ->andWhere(["<>", "source", "tuttocartucce"])
            ->andWhere([
              "or",
              ["replace(sku, '-', '')" => $sku],
              ["like", "replace(source_key, '-', '')", "/$sku"],
              ["like", "concat(' ', replace(title, '-', ''), ' ')", " $sku "],
            ]);

          $dataProviderProdottiSource = new ActiveDataProvider([
            'query' => $queryProdotti,
            'sort' => false
          ]);

          $create_ok = \Yii::$app->db->createCommand("select count(*) from toner_source_modelli m join toner_source_product_modelli p on p.id_modello = m.id where p.id_product = $id and m.id_modello is null")->queryScalar();

          $n = \Yii::$app->db->createCommand("select count(*) from toner_source_product_join j where j.id_source_product = $id")->queryScalar();

          if ($n>0)
            $create_ok = 99;

        }
        else {
          $dataProviderProdottiSource = null;
        }

        return $this->render('view', [
            'model' => $model,
            'dataProviderModelli' => $dataProviderModelli,
            'dataProviderProdotti' => $dataProviderProdotti,
            'searchModelProdotti' => $searchModelProdotti,
            'dataProviderProdottiSource' => $dataProviderProdottiSource,
            'create_ok' => $create_ok
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
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
