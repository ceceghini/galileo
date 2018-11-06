<?php

namespace app\controllers;

use Yii;
use yii\console\Controller;

class FeedController extends Controller {

  // Elenco dei modelli
  public function actionStampapeModelli() {

    $_modelli = \Yii::$app->db->createCommand(
      "select m.nome as modello, m.serie, m.tipologia, m.marca, id_serie, id, photo
         from toner_modelli m
        where enabled = 1"
    )->query();

    $ret = array();

    foreach ($_modelli as $_modello) {

      $marca = $_modello["marca"];
      $tipologia = $_modello["tipologia"];
      $serie = $_modello["serie"];
      $modello = $_modello["modello"];

      $modello = str_replace("$serie-", "", $modello);
      if ($serie != $modello) {
        $n = strlen($serie);
        if (substr($modello, 0, $n)==$serie) {
          $m = strlen($modello);
          $modello = substr($modello, $n, $m-$n);
        }
      }

      //$ret[$marca][$tipologia][$serie][] = $modello;
      $ret[$marca][$tipologia][$serie]["id"] = $_modello["id_serie"];
      $ret[$marca][$tipologia][$serie]["modelli"][$_modello["id"]]["modello"] = $modello;
      $ret[$marca][$tipologia][$serie]["modelli"][$_modello["id"]]["photo"] = $_modello["photo"];

    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $ret;

  }

  // Elenco prodotti compatibili
  public function actionStampapeProdotti() {

    /*$_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1 and (sku = '8286B006')"
    )->queryAll();*/
    $_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort, p.secondario, originale_ean
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1"
    )->queryAll();

    //$ret = array();
    foreach($_prodotti as $k=>&$p) {

      $product_modelli = \app\models\Toner\ProductModelli::find()
        ->joinWith("modello")
        ->andWhere(["id_prodotto" => $p["id"]])
        ->andWhere(["!=", "disabled", 1])
        ->select(["id_modello"])
        ->asArray()
        ->all();

      $modelli = array();

      foreach ($product_modelli as $value) {
        $modelli[] = $value["id_modello"];
      }

      $p["modelli"] = $modelli;

      if (strpos($p["compatibile_url_foto"], "/photo/compatibili/")===false) {
        $photo = "photo/compatibili_generic/".$p["tipologia"]."_".$p["colore"].".jpg";
        $photo = strtolower($photo);
        if (file_exists("/opt/galileo/$photo"))
          $p["compatibile_url_foto"] = "https://galileo.pointec.it/$photo";
        else
          $p["compatibile_url_foto"] = null;
      }

      else
        $p["compatibile_url_foto"] = "https://galileo.pointec.it".$p["compatibile_url_foto"];

      if (strpos($p["originale_url_foto"], "/photo/originali/")===false)
        $p["originale_url_foto"] = null;
      else
        $p["originale_url_foto"] = "https://galileo.pointec.it".$p["originale_url_foto"];

      $oem = explode("#", $p["part_number"]);

      $oem = array_unique($oem);
      $oem = array_filter($oem);

      $p["part_number"] = $oem;

    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $_prodotti;

    print_r($_prodotti);

  }

  public function actionTonerperProdotti() {

    /*$_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1 and (sku = '8286B006')"
    )->queryAll();*/
    $_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo_tonerper as compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort, p.secondario
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1"
    )->queryAll();

    //$ret = array();
    foreach($_prodotti as $k=>&$p) {

      $product_modelli = \app\models\Toner\ProductModelli::find()
        ->joinWith("modello")
        ->andWhere(["id_prodotto" => $p["id"]])
        ->andWhere(["!=", "disabled", 1])
        ->select(["id_modello"])
        ->asArray()
        ->all();

      $modelli = array();

      foreach ($product_modelli as $value) {
        $modelli[] = $value["id_modello"];
      }

      $p["modelli"] = $modelli;

      if (strpos($p["compatibile_url_foto"], "/photo/compatibili/")===false)
        $p["compatibile_url_foto"] = null;
      else
        $p["compatibile_url_foto"] = "https://galileo.pointec.it".$p["compatibile_url_foto"];

      if (strpos($p["originale_url_foto"], "/photo/originali/")===false)
        $p["originale_url_foto"] = null;
      else
        $p["originale_url_foto"] = "https://galileo.pointec.it".$p["originale_url_foto"];

      $oem = explode("#", $p["part_number"]);

      $oem = array_unique($oem);
      $oem = array_filter($oem);

      $p["part_number"] = $oem;

    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $_prodotti;

    print_r($_prodotti);

  }

  public function actionEcolorsProdotti() {

    /*$_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1 and (sku = '8286B006')"
    )->queryAll();*/
    $_prodotti = \Yii::$app->db->createCommand(
      "select p.id, sku, colore, resa, tipologia, ean, m.nome as marca
            , compatibile, compatibile_prezzo_ecolors, compatibile_prezzo, compatibile_url_foto
            , originale, originale_prezzo, originale_disponibile, originale_url_foto
            , p.part_number, p.sort, p.secondario, originale_ean
         from toner_product p
           join toner_marche m on p.id_marca = m.id
        where enabled = 1"
    )->queryAll();

    //$ret = array();
    foreach($_prodotti as $k=>&$p) {

      $product_modelli = \app\models\Toner\ProductModelli::find()
        ->joinWith("modello")
        ->andWhere(["id_prodotto" => $p["id"]])
        ->andWhere(["!=", "disabled", 1])
        ->select(["id_modello"])
        ->asArray()
        ->all();

      $modelli = array();

      foreach ($product_modelli as $value) {
        $modelli[] = $value["id_modello"];
      }

      $p["modelli"] = $modelli;

      if (strpos($p["compatibile_url_foto"], "/photo/compatibili/")===false)
        $p["compatibile_url_foto"] = null;
      else
        $p["compatibile_url_foto"] = "https://galileo.pointec.it".$p["compatibile_url_foto"];

      if (strpos($p["originale_url_foto"], "/photo/originali/")===false)
        $p["originale_url_foto"] = null;
      else
        $p["originale_url_foto"] = "https://galileo.pointec.it".$p["originale_url_foto"];

      $oem = explode("#", $p["part_number"]);

      $oem = array_unique($oem);
      $oem = array_filter($oem);

      $p["part_number"] = $oem;

    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $_prodotti;

    print_r($_prodotti);

  }

  public function actionLacartModelli() {

    $modelli = \Yii::$app->db->createCommand(
      "select distinct marca, m.tipologia, serie, nome, m.id
         from toner_modelli m
           join toner_product_modelli pm on m.id = pm.id_modello
           join toner_product p on p.id = pm.id_prodotto
           join toner_product_sale s on p.id = s.id_product and s.tipologia = 'COMPATIBILE' and s.period = '3Y'
        where m.enabled = 1
          and pm.disabled = 0
          and p.compatibile = 1"
    )->query();

    $ret = array();
    foreach ($modelli as $_modello) {

      $marca = $_modello["marca"];
      $tipologia = $_modello["tipologia"];
      $serie = $_modello["serie"];
      $modello = $_modello["nome"];

      $modello = str_replace("$serie-", "", $modello);
      if ($serie != $modello) {
        if (strpos($modello, $serie)!==false)
          $modello = preg_replace("/$serie/", "", $modello, 1);
      }

      $ret[$marca][$tipologia][$serie][$modello] = $_modello["id"];
    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $ret;

  }

  public function actionLacartProdotti() {

      $prodotti = \Yii::$app->db->createCommand(
        "select distinct p.tipologia as tipologia_product
              , m.marca
              , m.id_serie as id_serie
              , p.id as id_prodotto
              , p.sku
              , p.colore
              , p.resa
              , p.tipologia as tipologia
              , p.compatibile_prezzo as prezzo_compatibile
              , p.ean
              , p.compatibile_url_foto as url_foto_compatibile
           from toner_product p
             join toner_product_modelli pm on p.id = pm.id_prodotto
             join toner_modelli m on m.id = pm.id_modello
             join toner_product_sale s on p.id = s.id_product and s.tipologia = 'COMPATIBILE' and s.period = '3Y'
          where p.enabled = 1
            and p.compatibile = 1
            and pm.disabled = 0"
      )->queryAll();

      foreach($prodotti as $k=>$p) {

        $prodotto = \app\models\Toner\Product::findOne($p["id_prodotto"]);

        if (strpos($p["url_foto_compatibile"], "/photo/compatibili/")===false)
          $p["url_foto_compatibile"] = null;
        else
          $prodotti[$k]["url_foto_compatibile"] = "https://galileo.pointec.it".$p["url_foto_compatibile"];

        $data = $this->lacartData($p["id_serie"], $prodotto);
        $prodotti[$k]["title"] = $data["title"];
        $prodotti[$k]["id_modelli"] = $data["ids"];
        //$prodotti[$k]["title"] = $this->getTitleLacartuc($p["id_serie"], $prodotto);
        //$prodotti[$k]["modelli"] = $this->getModelliLacartuc($p["id_prodotto"], $p["id_serie"]);

      }

      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

      //print_r($prodotti);

      return $prodotti;

  }

  public function actionEurocaliCategory() {

    $categoria = \app\models\Eurocali\Category::find()
      ->orderBy("id_parent")
      ->asArray(true)
      ->all();

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $categoria;

  }

  public function actionEurocaliProducts() {

    $prodotti = \app\models\Eurocali\Product::find()
      ->select("id, brand, short_description, description, price, title, json_data")
      ->asArray(true)
      ->andWhere(["id" => 26])
      ->limit(10)
      ->all();

    foreach ($prodotti as &$value) {

      $category = \app\models\Eurocali\ProductCategory::find()
        ->andWhere(["id_product" => $value["id"]])
        ->select("id_category")
        ->asArray(true)
        ->all();

      foreach ($category as $c) {
        $value["category"][] = $c["id_category"];
      }

      $value["json_data"] = json_decode($value["json_data"], true);

    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $prodotti;

  }

  public function actionEurocaliPhoto() {

    $ret = array();

    $files = scandir("/opt/galileo/photo/led/others");
    foreach ($files as $file) {
      if ($file=="."|| $file=="..")
        continue;
      $ret["others"][] = str_replace("/opt/galileo", "", $file);
    }

    $files = scandir("/opt/galileo/photo/led/pdf");
    foreach ($files as $file) {
      if ($file=="."|| $file=="..")
        continue;
      $ret["pdf"][] = str_replace("/opt/galileo", "", $file);
    }

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    return $ret;

  }

  private function lacartData($id_serie, $_p) {

    $modelli = \Yii::$app->db->createCommand(
      "select m.nome, m.id
         from toner_modelli m
           join toner_product_modelli pm on m.id = pm.id_modello
        where pm.disabled = 0
          and pm.id_prodotto = :id_prodotto
          and m.id_serie = :id_serie")
      ->bindValue("id_prodotto", $_p->id)
      ->bindValue("id_serie", $id_serie)
      ->queryAll();

    $serie = \app\models\Toner\Serie::findOne($id_serie);
    $marca = \app\models\Toner\Marche::findOne($serie->id_marca);

    $nome_serie = $serie->nome;
    $nome_marca = $marca->nome;
    $nome_modelli = "";
    $ids = array();

    foreach($modelli as $modello) {

      $nome = trim(strtoupper($modello["nome"]));

      $nome = str_replace("$nome_serie-", "", $nome);
      if ($nome_serie != $nome)
        $nome = str_replace("$nome_serie", "", $nome);

      $nome_modelli .= " ".$nome;

      $ids[] = $modello["id"];
    }

    if ($nome_serie == "ALTRI MODELLI")
      $nome_serie = "";

    $nome_serie = str_replace("$nome_marca-", "", $nome_serie);
    $nome_serie = str_replace("$nome_marca", "", $nome_serie);

    $title = $nome_marca . " " . $nome_serie. $nome_modelli;

    $title = preg_replace('/\s+/', ' ',$title);

    //$title2 = "{$_p->tipologia} {$_p->colore} compatibile con {$_p->sku} per $title";
    $title2 = "{$_p->tipologia} {$_p->colore} compatibile per $title";

    $title2 = strtoupper($title2);

    return [
      "title" => $title2,
      "ids" => $ids
    ];

  }

}
