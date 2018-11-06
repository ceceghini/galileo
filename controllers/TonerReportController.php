<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

class TonerReportController extends Controller
{

  public function actionPrezziOffertecartucce() {

    return $this->returnReport(\app\models\Toner\Report\PriceOffertecartucce::find(), "toner-product", "Prezzi OfferteCartucce in verdestampa");

  }

  public function actionPrezziTuttocartucce() {

    return $this->returnReport(\app\models\Toner\Report\PriceTuttocartucce::find(), "toner-product", "Prezzi TuttoCartucce");

  }

  public function actionPrezziPuntorigenera() {

    return $this->returnReport(\app\models\Toner\Report\PricePuntorigenera::find(), "toner-product", "Prezzi Puntorigenera");

  }

  public function actionPrezziTonerper() {

    return $this->returnReport(\app\models\Toner\Report\PriceTonerper::find(), "toner-product", "Prezzi Tonerpertutti.it");

  }

  public function actionPrezziEcolors() {

    return $this->returnReport(\app\models\Toner\Report\PriceEcolors::find(), "toner-product", "Prezzi Ecolors.it");

  }

  public function actionSaleCompatibile1y() {

    return $this->returnReport(\app\models\Toner\Report\SaleCompatibile1y::find(), "toner-report/clienti-prodotti", "Prodotti compatibili più venduti 1Y");

  }

  private function returnReportSql($sql, $url, $title) {

    $result = \Yii::$app->db->createCommand($sql)->queryAll();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $result,
      'pagination' => [
        'pageSize' => 100
      ]
    ]);

    return $this->render('generic-sql', [
        'dataProvider' => $dataProvider,
        'url' => $url,
        'title' => $title
    ]);

  }

  private function returnReportSqlOdoo($sql, $url, $title) {

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $result,
      'pagination' => [
        'pageSize' => 100
      ]
    ]);

    return $this->render('generic-sql', [
        'dataProvider' => $dataProvider,
        'url' => $url,
        'title' => $title
    ]);

  }

  private function returnReport($query, $url, $title) {

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100
      ]
    ]);

    return $this->render('generic', [
        'dataProvider' => $dataProvider,
        'url' => $url,
        'title' => $title
    ]);

  }

  public function actionProdottiAltaCapacita() {

    $sql = "select distinct p.id, p.sku, m.nome as marca, p.tipologia, p.colore, p.resa
     , p2.sku as sku2, p2.tipologia as tipologia2, p2.colore as colore2, p2.resa as resa2
  from toner_product p
    join toner_marche m on p.id_marca = m.id
    join toner_product_modelli pm on p.id = pm.id_prodotto
    join toner_product_modelli pm2 on pm.id_modello = pm2.id_modello
    join toner_product p2 on pm2.id_prodotto = p2.id
 where pm.id_prodotto <> pm2.id_prodotto
   and p.id_marca = p2.id_marca
   and p.tipologia = p2.tipologia
   and p.colore = p2.colore
   and (p.compatibile = 1 or p2.compatibile = 1) and p.sku = '70C2HKE'";
   

   return $this->returnReportSql($sql, "toner-product", "Verifica prodotti alta capacita");

  }

  public function actionModelliConPiuProdotti() {

    $sql = "select m.id, m.marca, m.serie, m.nome as modello, p.tipologia, p.colore, count(*) as n
  from toner_modelli m
    join toner_product_modelli pm on m.id = pm.id_modello
    join toner_product p on p.id = pm.id_prodotto
 where pm.disabled = 0
   and p.enabled = 1
   and m.enabled = 1
  group by m.id, m.marca, m.serie, m.nome, p.tipologia, p.colore
  having count(*) > 1";

    return $this->returnReportSql($sql, "toner-modelli", "Modelli con più prodotti");

  }

  public function actionClientiProdotti($id) {

    $_product = \app\models\Toner\Product::findOne($id);

    $sql = "select lower(pa.email), pa.name, round(sum(product_uom_qty)) as qty
  from sale_report s
    join product_product p on s.product_id = p.id
    join res_partner pa on s.partner_id = pa.id
   where p.default_code = 'TC/{$_product->sku}'
     and state = 'confirmed'
     group by lower(pa.email), pa.name";

     return $this->returnReportSqlOdoo($sql, "", "Elenco clienti che hanno acquistato [TC/{$_product->sku}]");

  }

  /*public function actionPriceSale($filter=null) {

    $searchModel = new \app\models\Toner\Report\PriceSaleSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    if ($filter=="puntorigenera") {
      $dataProvider->query->andWhere([">", "prezzo_avg", 0]);
    }

    if ($filter=="verdestampa") {
      $dataProvider->query
        ->andWhere([">", "prezzo_avg2", 0])
        ->andWhere([">", "n_verdestampa", 2]);
    }

    return $this->render('price-sale', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);

  }*/

  /*public function actionPriceVerdestampa($filter=null) {

    $searchModel = new \app\models\Toner\Report\PriceVerdestampaSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('price-verdestampa', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);

  }*/

  /*public function actionPriceSaleSimulate() {

    $searchModel = new \app\models\Toner\Report\PriceSaleSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query->andWhere([">", "prezzo_avg", 0]);

    return $this->render('price-sale-simulate', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);

  }*/

  public function actionSourcePrezzo() {

    $sql = "select *
      from toner_report_price_scostamento
        where scostamento < 0.9
        order by 8";

    return $this->returnReportSql($sql, "toner-product", "Scostamento prezzi");

  }

  // Prodotti sorgenti collegati maggiore di 3
  public function actionSourceNumber() {

    $sql = "select p.id, p.sku, s.source, count(*) as numero
from toner_product p
  join toner_source_product_join sp on p.id = sp.id_product
  join toner_source_product s on sp.id_source_product = s.id
  where sp.disabled = 0
group by p.id, p.sku, s.source
having count(*) > 3
order by 4 desc";

    $result = \Yii::$app->db->createCommand($sql)->queryAll();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $result,
    ]);

    return $this->render('sourcenumber', [
        'dataProvider' => $dataProvider,
    ]);

  }



}
