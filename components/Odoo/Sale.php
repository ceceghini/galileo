<?php

namespace app\components\Odoo;

class Sale {

  private $client;

  function __construct() {

    $this->client = new Client();

  }

  // Caricamento dati di vendita
  public function import() {

    $sql = "truncate table toner_product_sale";

    \Yii::$app->db->createCommand($sql)->query();

    $sql = "select p.default_code, sum(product_uom_qty) as qty, sum(price_total) as total
  from sale_report s
    join product_product p on s.product_id = p.id
   where (p.default_code like 'TC/%' or p.default_code like 'TO/%')
     and state = 'confirmed'
     and s.date > (current_date - INTERVAL '36 months')
    group by p.default_code";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();
    foreach ($result as $value) {
      $this->processSale($value, "3Y");
    }

    $sql = "select p.default_code, sum(product_uom_qty) as qty, sum(price_total) as total
  from sale_report s
    join product_product p on s.product_id = p.id
   where (p.default_code like 'TC/%' or p.default_code like 'TO/%')
     and state = 'confirmed'
     and s.date > (current_date - INTERVAL '12 months')
    group by p.default_code";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();
    foreach ($result as $value) {
      $this->processSale($value, "1Y");
    }

  }

  // Caricamento dati di vendita singoli
  private function processSale($value, $period) {

    if (strpos($value["default_code"], "TO/")!==false) {
      $tipologia = "ORIGINALE";
      $sku = str_replace("TO/", "", $value["default_code"]);
    }

    if (strpos($value["default_code"], "TC/")!==false) {
      $tipologia = "COMPATIBILE";
      $sku = str_replace("TC/", "", $value["default_code"]);
    }

    if (!$tipologia) {
      echo "Tipologia non codificata\n";
      print_r($value);
    }

    $product = \app\models\Toner\Product::findOne(["sku" => $sku]);

    if (!$product){
      return;
    }

    /*$sale = \app\models\Toner\ProductSale::findOne([
      "id_product" => $product->id,
      "period" => $period,
      "tipologia" => $tipologia
    ]);*/

    //$save = false;
    //if (!$sale) {
      $sale = new \app\models\Toner\ProductSale();
      $sale->id_product = $product->id;
      $sale->period = $period;
      $sale->tipologia = $tipologia;
      $save = true;
    //}

    //if ($sale->qty != (int)$value["qty"]) {
      $sale->qty = (int)$value["qty"];
    //  $save = true;
    //}

    //if ($sale->total != $value["total"]) {
      $sale->total = $value["total"];
    //  $save = true;
    //}

    //if ($save) {
      if (!$sale->save()) {
        print_r($sale->getErrors());
      }
    //}

  }

}
