<?php

namespace app\controllers;

use Yii;
use app\models\Toner\Product;
use app\models\Toner\ProductSearch;
use app\models\Toner\ProductSaleSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use app\models\Toner\ProductModelli;
use app\models\ProductShop;
use app\models\Toner\Prezzi;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Toner\ProductRefillSearch;
use app\models\Toner\ProductPrSearch;
use yii\helpers\Json;
use \yii\web\Response;

use yii\data\ArrayDataProvider;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class TonerProductController extends Controller
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
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = false;
        $dataProvider->pagination->pageSize=100;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexNonCollegati()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->joinWith("sourceProductsJoin")
          ->andWhere(["toner_source_product_join.id_product" => null]);
        $dataProvider->sort = false;
        $dataProvider->pagination->pageSize=100;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionJoinModello($id, $id_modello) {

      $model = \app\models\Toner\Modelli::findOne($id_modello);
      $model->join($id);

      return $this->redirect(['view', 'id' => $id]);

    }

    public function actionUnjoinModello($id, $id_modello) {

      $model = \app\models\Toner\Modelli::findOne($id_modello);
      $model->unJoin($id);

      return $this->redirect(['view', 'id' => $id]);

    }

    public function actionJoin($id, $id_product) {

      $model = \app\models\Toner\Source\Product::findOne($id);
      $model->join($id_product);

      return $this->redirect(['view', 'id' => $id_product]);

    }

    public function actionUnjoin($id, $id_product) {

      $model = \app\models\Toner\Source\Product::findOne($id);
      $model->unJoin($id_product);

      return $this->redirect(['view', 'id' => $id_product]);

    }

    /**
     * Displays a single Product model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id, $tab=null)
    {

        $model = $this->findModel($id);

        // Modelli
        $searchModelModelli = new \app\models\Toner\ModelliSearch();
        $dataProviderModelli = $searchModelModelli->search(Yii::$app->request->queryParams);
        $dataProviderModelli->sort = false;
        $dataProviderModelli->pagination->pageSize=10;
        if (!isset(Yii::$app->request->queryParams["ModelliSearch"]))
          $modelliSearch = array();
        else
          $modelliSearch = array_filter(Yii::$app->request->queryParams["ModelliSearch"]);
        if (empty($modelliSearch)) {
          $dataProviderModelli->query->joinWith("products")
            ->andWhere(["id_prodotto" => $id]);
        }

        // Vendite
        $dataProviderVendite = new ActiveDataProvider([
          'query' => $model->getProductSales(),
          'sort' => false
        ]);

        // Prodotti collegati
        $searchModelProdotti = new \app\models\Toner\Source\ProductSearch();
        $dataProviderProdotti = $searchModelProdotti->search(Yii::$app->request->queryParams);
        $dataProviderProdotti->sort = false;
        $dataProviderProdotti->pagination->pageSize=10;

        if (!isset(Yii::$app->request->queryParams["ProductSearch"]))
          $productSearch = array();
        else
          $productSearch = array_filter(Yii::$app->request->queryParams["ProductSearch"]);

        if (empty($productSearch)) {
          $dataProviderProdotti->query->joinWith("sourceProductsJoin")
            ->andWhere(["id_product" => $id]);
        }

        // Prezzi verdestampa
        $dataProviderVS = new ActiveDataProvider([
          'query' => $model->getProductPrezzi(),
          'sort' => false
        ]);

        return $this->render('view', [
            'model' => $model,
            'dataProviderModelli' => $dataProviderModelli,
            'dataProviderVendite' => $dataProviderVendite,
            'dataProviderProdotti' => $dataProviderProdotti,
            'searchModelProdotti' => $searchModelProdotti,
            'searchModelModelli' => $searchModelModelli,
            'dataProviderVS' => $dataProviderVS
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id_source = null, $id_prodotto = null)
    {
      $request = Yii::$app->request;
      $model = new Product();
      $model->manuale = 1;

      if($request->isAjax){
        Yii::$app->response->format = Response::FORMAT_JSON;

        if($request->isGet){
          // Valorizzo i valodi di default sulla base del modello sorgente
          if ($id_source) {
            if(!$this->defaultFromSource($id_source, $model)) {
              Yii::$app->response->format = Response::FORMAT_JSON;
              return ['forceClose'=>true,'forceReload'=>'#crud-datatable-prodotti-pjax'];
            }
          }

          return [
            'title'=> "Nuovo prodotto",
            'content'=> $this->renderAjax('create', [
                'model' => $model,
            ]),
            'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                       Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
          ];
        }else if($model->load($request->post()) && $model->save()){

          if ($id_source) {
            $source = \app\models\Toner\Source\Product::findOne($id_source);
            $source->join($model->id);
            $source->joinModelli($model->id);
          }

          if ($id_prodotto) {
            $source = $this->findModel($id_prodotto);
            $source->copyModelli($model->id);
          }

          Yii::$app->response->format = Response::FORMAT_JSON;
          return ['forceClose'=>true,'forceReload'=>'#crud-datatable-prodotti-pjax'];
        }else{
          return [
            'title'=> "Nuovo prodotto",
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

      $source = \app\models\Toner\Source\Product::findOne($id_source);

      if ($source->source=="puntorigenera")
        return true;

      $n = \Yii::$app->db->createCommand("select count(*) from toner_source_modelli m join toner_source_product_modelli p on p.id_modello = m.id where p.id_product = $id_source and m.id_modello is null")->queryScalar();
      if ($n > 0)
        return false;

      // Valori di default
      $model->sku = $source->sku;
      $source_modelli = $source->modelli;
      $model->id_marca = $source->modelli[0]->sourceSerie->sourceMarca->id_marca;
      $model->colore = $source->getProductColor();
      if (strpos($source->description, "TONER")!==false)
        $model->tipologia = "TONER";
      if (strpos($source->description, "CARTUCCIA")!==false)
        $model->tipologia = "CARTUCCIA";
      if (strpos($source->description, "TAMBURO")!==false)
        $model->tipologia = "TAMBURO";

      return true;

    }

    /**
     * Updates an existing Product model.
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
                  'title'=> "Modifica prodotto ".$model->sku,
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

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionSale() {

      $searchModel = new ProductSaleSearch();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      return $this->render('sale', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider,
      ]);

    }

    /*public function actionFind($q = null) {

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $out = ['results' => ['id' => '', 'text' => '']];

    if (!is_null($q)) {
        $data = Product::find()
          ->andWhere(["like", "sku", $q])
          ->select(["id", "sku as text"])
          ->asArray()
          ->limit(20)
          ->all();
        $out['results'] = array_values($data);
    }

    return $out;
}*/

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
     * Lists all Product models.
     * @return mixed
     */
    public function actionCompatibiliSenzaFoto()
    {

      $sql = "select p.id, p.sku, m.nome as marca, p.tipologia, compatibile_url_foto, null as compatibile_foto
                from toner_product p
                  join toner_marche m on p.id_marca = m.id
               where p.compatibile = 1";

      $result = \Yii::$app->db->createCommand($sql)->queryAll();

      foreach ($result as $key => $value) {

        if ($value["compatibile_url_foto"]) {
          $file = "/opt/galileo".$value["compatibile_url_foto"];
          $size = getimagesize($file);
          if ($size[0]>=500 && $size[1]>=500) {
            unset($result[$key]);
          }
          else {
            $result[$key]["compatibile_foto"] = "small";
          }
        }
        else {
          $result[$key]["compatibile_foto"] = "nofoto";
        }

        //$size = getimagesize($file);

        //echo $file."\n";
        //print_r($size);

        //print $value["compatibile_url_foto"]."\n";

      }

      $dataProvider = new ArrayDataProvider([
        'allModels' => $result,
      ]);

      return $this->render('nofoto', [
          'dataProvider' => $dataProvider,
          'url' => "toner-product"
      ]);

    }

    /** Verifica i prodotto da eliminare in formato json **/
    public function actionDaEliminare() {

      $request = Yii::$app->request;
      $model = new \app\models\Toner\Report\DaEliminare();
      if ($model->load($request->post())) {

        $skus = json_decode($model->skus, true);
        $compatibili = array();
        $originali = array();
        foreach ($skus as $sku => $id) {

          if (substr($sku, 0, 3)=="TC/")
            $compatibili[] = "'".str_replace("TC/", "", $sku)."'";
          else
            $originali[] = "'".str_replace("TO/", "", $sku)."'";
        }

        $where = implode(",", $compatibili);

        $sql = "select p.id, p.sku, m.nome as marca, p.tipologia, s.period, s.qty
                  from toner_product p
                    left outer join toner_product_sale s on p.id = s.id_product
                    join toner_marche m on p.id_marca = m.id
                 where s.tipologia = 'COMPATIBILE' and p.sku in ($where)";

        //echo $sql;

        $result = \Yii::$app->db->createCommand($sql)->queryAll();

        $dataProvider = new ArrayDataProvider([
          'allModels' => $result,
          'pagination' => [
            'pageSize' => 200
          ]
        ]);

      }
      else {
        $dataProvider = null;
      }

      return $this->render('daeliminare', [
          'dataProvider' => $dataProvider,
          'model' => $model,
          'url' => "toner-product"
      ]);

    }

}
