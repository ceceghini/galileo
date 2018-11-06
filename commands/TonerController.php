<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\components\Util;

class TonerController extends Controller {

  private $ids_join = array();
  private $exclude_sku = [
    'EP-L',
    '301',
    '710',
    '251',
    'EP-N'
  ];

  private $md5_question_mark = "3e116426a188a2473aa7d55b543ae4bf";

  private $moltiplica_tonerper = 1.06;

  /**
    Elaborazione dei modelli, impsta tipologia e li abilita
  **/
  public function actionProcessModelli() {

    $_modelli = \app\models\Toner\Modelli::find()
      //->andWhere(["nome" => "TN-241Y"])
      ->all();

    foreach ($_modelli as $_modello) {

      foreach ($_modello->getProductModelli()->all() as $_pm) {

        if (!$_pm->prodotto->enabled)
          continue;

        $disabled = false;

        //echo $_pm->prodotto->sku."\n";

        if ($_modello->marca != $_pm->prodotto->marca->nome) {

          //echo $_pm->prodotto->sku." marca diversa [{$_modello->marca}] [{$_pm->prodotto->marca->nome}]\n";

          $n = \app\models\Toner\ProductModelli::find()
            ->joinWith("prodotto")
            ->andWhere([
              "id_modello" => $_modello->id,
              "id_marca" => $_modello->getSerie()->one()->id_marca,
              "enabled" => 1,
              "colore" => $_pm->prodotto->colore,
              "tipologia" => $_pm->prodotto->tipologia
            ])
            ->count();

          if ($n>0)
            $disabled = true;

        }

        if ($_pm->disabled != $disabled) {

          echo "ProdottoModello disabilitato: [{$_modello->marca}] [{$_modello->nome}] [{$_pm->prodotto->sku}] [$disabled]\n";

          $_pm->disabled = $disabled;
          $_pm->save();

        }

      }

      $_modello->setEnabled();
      $_modello->setTipologia();

      $_modello->downloadPhoto();

    }

  }

  /**
    Elaborazione dei prodotti, imposta prezzo compatibile e originale
  **/
  public function actionProcessProduct() {

    $_products = \app\models\Toner\Product::find()
      //->andWhere(["sku" => "C13S051100"])
      //->limit(100)
      ->all();

    foreach ($_products as $_p) {

      $this->processSingleProduct($_p);

    }

  }

  // Elaborazione di un singolo prodotto
  private function processSingleProduct($_p) {

    // Sort
    $sort = array();
    // Sort tipologia
    switch ($_p->tipologia) {
      case 'ALTRO':
        $sort[] = 0;
        break;
      case 'CARTUCCIA':
        $sort[] = 9;
        break;
      case 'MULTIPACK':
        $sort[] = 5;
        break;
      case 'NASTRI':
        $sort[] = 9;
        break;
      case 'TAMBURO':
        $sort[] = 7;
        break;
      case 'TONER':
        $sort[] = 9;
        break;
    }
    // Sort resa
    /*if ($_p->tipologia == "TONER" || $_p->tipologia == "CARTUCCIA") {

      $resa = str_replace("PAGINE", "", strtoupper($_p->resa));
      $resa = str_replace("ML", "", $resa);

      if (is_numeric(trim($resa))) {

        $sort[] = str_pad($resa, 8, '0', STR_PAD_LEFT);

      }

    }

    //$_p->sort = implode(".", $sort);

    // Elaborazione prezzi
    $save = false;

    $sort = implode(".", $sort);
    if ($_p->sort != $sort) {
      $_p->sort = $sort;
      $save= true;
    }*/

    //if($this->prezzoOriginale($_p)) $save = true;

    //if($this->prezzoCompatibile($_p)) $save = true;

    //if($this->prezzoCompatibileTonerper($_p)) $save = true;

    //if($this->prezzoCompatibileEcolors($_p)) $save = true;

    //$save = true;

    if ($save) {
      //echo "$_p->sku salvato\n";
      if (!$_p->save()) {
        echo "Errore salvataggio [$_p->sku]\n";
        print_r($_p->getErrors());
      }
    }

    // Elaborazione foto originale
    if ($_p->originale_url_foto) {

      if (strpos($_p->originale_url_foto, "/photo/originali/")===false) {

        $info = pathinfo($_p->originale_url_foto);

        $tipologia = strtolower($_p->tipologia);

        if (isset($info["extension"])) {
          $dest = "/photo/originali/$tipologia-originale-".str_replace("/", "-", $_p->sku).".".$info["extension"];

          $proxy = new \app\components\Proxy();
          $proxy->downloadFile($_p->originale_url_foto, "/opt/galileo$dest");

          if (filesize("/opt/galileo$dest") > 0) {

            $_p->originale_url_foto = $dest;
            $_p->save();

          }
        }

      }

    }

    // Foto compatibile
    if ($_p->compatibile_url_foto) {

      if (strpos($_p->compatibile_url_foto, "/photo/compatibili/")===false) {

        $info = pathinfo($_p->compatibile_url_foto);

        if (isset($info["extension"])) {
          $dest = "/photo/compatibili/toner-compatibile-".str_replace("/", "-", $_p->sku).".".$info["extension"];

          $proxy = new \app\components\Proxy();
          $proxy->downloadFile($_p->compatibile_url_foto, "/opt/galileo$dest");

          $md5 = md5(file_get_contents("/opt/galileo$dest"));

          if (filesize("/opt/galileo$dest") > 0 && $md5 != $this->md5_question_mark) {

            $_p->compatibile_url_foto = $dest;
            $_p->save();

          }
        }

      }

    }

  }

  // Elaborazione del prezzo compatibile
  private function prezzoCompatibile(&$_p) {

    $now = date('Y-W');
    $update = date('Y-W', strtotime($_p->compatibile_prezzo_updated));

    if ($now <= $update && $_p->compatibile_prezzo > 0) {
      return false;
    }

    $source = null;

    if ($_p->manuale)
      return; // Prodotto aggiornato manualmente

    $prezzo_compatibile = 0;

    $_price = $_p->getPrezzoVS("OfferteCartucce.com");

    if ($_price) {
      $prezzo_compatibile = round($_price * 1.06, 6);
      $source = "offertecartucce.com VS";
      //echo $source."\n";
    }

    if ($prezzo_compatibile == 0) {

      $_price = $_p->getPrezzoVS("TuttoCartucce.com");

      if ($_price) {
        if ($_p->tipologia == "TONER")
          $prezzo_compatibile = round($_price, 6);
        else
          $prezzo_compatibile = round($_price, 6);

        $source = "tuttocartucce.com VS";
        //echo $source."\n";
      }

    }

    if ($prezzo_compatibile == 0) {

      $source = $_p->getSource("tuttocartucce");

      if ($source) {
        if ($_p->tipologia == "TONER")
          $prezzo_compatibile = round($source["price"], 6);
        else
          $prezzo_compatibile = round($source["price"], 6);

        $source = "tuttocartucce.com";
        //echo $source." [$_price->prezzo_avg]\n";
      }

    }

    if ($prezzo_compatibile == 0) {

      $source = $_p->getSource("puntorigenera");

      if ($source) {
        if ($source["price"] > 2)
          $prezzo_compatibile = round($source["price"] * 2, 6);
        else
          $prezzo_compatibile = round($source["price"] * 6, 6);

        $source = "puntorigenera.it";
      }

    }

    if ($prezzo_compatibile == 0) {

      $_price = $_p->getPrezzoMedioVs();

      if ($_price) {
        $prezzo_compatibile = round($_price * 1.1, 6);

        $source = "source verdestampa avg";
      }

    }

    /*$source = $_p->getSource("puntorigenera");
    if ($source) {
      if ($source["price"] + )
    }*/

    if ($prezzo_compatibile == 0 && $_p->compatibile_prezzo > 0) {

      $_sale = \app\models\Toner\ProductSale::find()
        ->andWhere([
          "id_product" => $_p->id,
          "period" => "1Y",
          "tipologia" => "COMPATIBILE"
        ])->one();

      if (!$_sale) {
        //echo "Prezzo compatibile azzerato [$_p->sku]\n";
        //echo "'$_p->sku', ";
        $_p->compatibile_prezzo = 0;
        $_p->compatibile_prezzo_updated = date('Y-m-d');
        return true;
      }
      else {

        if ($_sale->qty <= 2) {
          $_p->compatibile_prezzo = 0;
          $_p->compatibile_prezzo_updated = date('Y-m-d');
          return true;
        }

        echo "Prezzo compatibile azzerato [$_p->sku] [{$_sale->qty}]\n";
      }
    }

    if ($prezzo_compatibile > 60)
      $prezzo_compatibile = round($prezzo_compatibile * 0.95, 6);

    if ($_p->compatibile_prezzo != $prezzo_compatibile && $prezzo_compatibile > 0) {
      echo "Prezzo compatibile modificato [$_p->sku] [$_p->compatibile_prezzo] [$prezzo_compatibile] [$source]\n";
      $_p->compatibile_prezzo = $prezzo_compatibile;
      $_p->compatibile_prezzo_updated = date('Y-m-d');
      $_p->compatibile_prezzo_source = $source;
      return true;
    }

    return false;

  }

  // Elaborazione del prezzo compatibile
  private function prezzoCompatibileTonerper(&$_p) {

    $now = date('Y-W');
    $update = date('Y-W', strtotime($_p->compatibile_prezzo_updated));

    if ($now <= $update && $_p->compatibile_prezzo_tonerper) {
      return false;
    }

    $source = $_p->getSource("puntorigenera");
    if ($source)
      $_price_pr = $source["price"];
    else
      $_price_pr = null;

    $_price = $_p->getPrezzoMinVs();

    $compatibile_prezzo_tonerper = null;

    if ($_price) {

      $_price = round($_price * $this->moltiplica_tonerper, 6);

      if ($_price_pr) {
        if ($_price > ($_price_pr * 1.2))
          $compatibile_prezzo_tonerper = $_price;
      }
      else
        $compatibile_prezzo_tonerper = $_price;


    }

    if (!$compatibile_prezzo_tonerper) {

      $_price = $_p->getPrezzoMinVs2();

      if ($_price) {

        $_price = round($_price * $this->moltiplica_tonerper, 6);

        if ($_price_pr) {
          if ($_price > ($_price_pr * 1.2))
            $compatibile_prezzo_tonerper = $_price;
        }
        else
          $compatibile_prezzo_tonerper = $_price;

      }

    }

    if (!$compatibile_prezzo_tonerper) {
      $compatibile_prezzo_tonerper = round($_p->compatibile_prezzo * $this->moltiplica_tonerper, 6);
    }

    if ($_p->compatibile_prezzo_tonerper != $compatibile_prezzo_tonerper && $compatibile_prezzo_tonerper > 0) {
      echo "Prezzo compatibile tonerper modificato [$_p->sku] [$_p->compatibile_prezzo_tonerper] [$compatibile_prezzo_tonerper]\n";
      $_p->compatibile_prezzo_tonerper = $compatibile_prezzo_tonerper;
      $_p->compatibile_prezzo_updated = date('Y-m-d');
      return true;
    }

    return false;

  }

  private function prezzoCompatibileEcolors(&$_p) {

    /*$now = date('Y-m');
    $update = substr($_p->compatibile_prezzo_updated, 0, 7);

    if ($now <= $update) {
      return false;
    }*/

    $source = $_p->getSource("puntorigenera");

    $compatibile_prezzo_ecolors = null;

    if ($source) {

      $compatibile_prezzo_ecolors = round($source["price"] * 1.2, 6);

    }

    if (!$compatibile_prezzo_ecolors) {

      $compatibile_prezzo_ecolors = round($_p->compatibile_prezzo, 6);

    }

    if ($_p->compatibile_prezzo_ecolors != $compatibile_prezzo_ecolors && $compatibile_prezzo_ecolors > 0) {
      echo "Prezzo compatibile ecolors modificato [$_p->sku] [$_p->compatibile_prezzo_ecolors] [$compatibile_prezzo_ecolors]\n";
      $_p->compatibile_prezzo_ecolors = $compatibile_prezzo_ecolors;
      $_p->compatibile_prezzo_updated = date('Y-m-d');
      return true;
    }

    return false;

  }

  private function prezzoOriginale(&$_p) {

    $save = false;

    $originale = [
      "prezzo" => 0,
      "disponibile" => 0,
      "ean" => null
    ];

    $source = $_p->getSource("supplies24");

    if ($source) {
      $originale["prezzo"] = $source["price"];
      $originale["disponibile"] = $source["qty"];
      $originale["ean"] = $source["html"];
    }

    if ($_p->originale_prezzo != $originale["prezzo"]) {
      //echo "Prezzo originale modificato [$_p->sku] [$_p->originale_prezzo] [$prezzo_originale]\n";
      $_p->originale_prezzo = $originale["prezzo"];
      $save = true;
    }

    if ($_p->originale_disponibile != $originale["disponibile"]) {
      //echo "Originale modificato [$_p->sku] [$_p->originale] [$originale]\n";
      $_p->originale_disponibile = $originale["disponibile"];
      $save = true;
    }

    if ($_p->originale_ean != $originale["ean"]) {
      //echo "Originale modificato [$_p->sku] [$_p->originale] [$originale]\n";
      $_p->originale_ean = $originale["ean"];
      $save = true;
    }

    return $save;

  }

  public function actionJoin() {

    $_prodotti = \app\models\Toner\Product::find()
      //->andWhere(["sku" => "B0910"])
      ->all();

    foreach ($_prodotti as $_prodotto) {

      $this->join($_prodotto);

    }

  }

  private function join($_p) {

    //$sql = "delete from toner_source_product_join where id_product = $_p->id and disabled = 0";
    //\Yii::$app->db->createCommand($sql)->execute();

    $sku_s = [$_p->sku => $_p->sku];
    $part_number = explode("#", $_p->part_number);
    foreach ($part_number as $value) {
      if ($value)
        if (!in_array($value, $this->exclude_sku))
          $sku_s[$value] = $value;
    }

    $sku_s = array_keys($sku_s);

    $this->ids_join = array();

    $this->getSourceSku($_p, $sku_s);

    $this->getSourceSkuLike($_p, $sku_s);

    // sku per epson
    if ($_p->id_marca == 5 and $_p->tipologia <> "MULTIPACK") {
      $sku_s = array();
      if (strpos($_p->sku, "C13")!==false) {
        $value = substr($_p->sku, 3, 4);
        $sku_s["EPSON% $value"] = "EPSON% $value";
        $sku_s[" value %EPSON"] = "$value %EPSON";
      }
      $part_number = explode("#", $_p->part_number);
      foreach ($part_number as $value) {
        if ($value)
          if (!in_array($value, $this->exclude_sku)) {
            if (strpos($value, "C13")!==false) {
              $value = substr($value, 3, 4);
              $sku_s["EPSON% $value"] = "EPSON% $value";
              $sku_s["$value %EPSON"] = "$value %EPSON";
            }
          }
      }
      //print_r($sku_s);
      $this->getSourceSkuLike($_p, $sku_s);
    }
    //

    foreach ($this->ids_join as $value) {
      $this->joinProduct($_p, $value);
    }

  }

  private function getSourceSku($_p, $sku_) {

    $sku = array();
    foreach ($sku_ as $value) {
      $sku[] = "'".str_replace("-", "", $value)."'";
    }

    $sql = "select id from toner_source_product where disabled = 0 and sku is not null and replace(sku, '-', '') in (".implode(",", $sku).")";

    $result = \Yii::$app->db->createCommand($sql)
      ->queryAll();

    if (sizeof($result)==0)
      return 0;

    foreach ($result as $value) {
      $this->ids_join[$value["id"]] = $value["id"];
    }

  }

  private function getSourceSkuLike($_p, $sku_) {

    foreach ($sku_ as $sku2) {

      $sku = "'%/".str_replace("-", "", $sku2)."'";

      $sql = "select id from toner_source_product where disabled = 0 and replace(source_key, '-', '') like ".$sku;
      $result = \Yii::$app->db->createCommand($sql)
        ->queryAll();
      foreach ($result as $value) {
        $this->ids_join[$value["id"]] = $value["id"];
      }

      $sku = "'% ".str_replace("-", "", $sku2)." %'";

      $sql = "select id, sku, title from toner_source_product where disabled = 0 and concat(' ', replace(title, '-', ''), ' ') like ".$sku;
      //echo $sql."\n";

      $result = \Yii::$app->db->createCommand($sql)
        ->queryAll();
      foreach ($result as $value) {
        $this->ids_join[$value["id"]] = $value["id"];
      }

      /*$sku = "'% ".str_replace("-", " ", $sku2)." %'";

      $sql = "select id, sku, title from toner_source_product where disabled = 0 and replace(title, '-', '') like ".$sku;
      $result = \Yii::$app->db->createCommand($sql)
        ->queryAll();
      foreach ($result as $value) {
        $this->ids_join[$value["id"]] = $value["id"];
      }*/

    }

  }

  private function joinProduct($_p, $value) {

    $join = \app\models\Toner\ProductJoin::findOne(["id_product" => $_p->id, "id_source_product" => $value]);
    if ($join)
      return;

    $join = new \app\models\Toner\ProductJoin();
    $join->id_product = $_p->id;
    $join->id_source_product = $value;
    $join->save();

  }

  public function actionVerifySourceSerie() {

    $source_products = \app\models\Toner\Source\Product::find()
      ->joinWith("sourceProductsJoin")
      ->andWhere(["source" => "tuttocartucce", "toner_source_product.disabled" => 0])
      ->andWhere(["toner_source_product_join.id_source_product" => null])
      ->all();

    $serie = array();

    foreach ($source_products as $source_product) {

      foreach ($source_product->modelli as $source_modello) {

        $source_serie = $source_modello->sourceSerie;

        if (!$source_serie->id_serie) {

          $serie[$source_serie["id"]] = "[{$source_serie->nome}] [{$source_serie->sourceMarca->nome}]";

        }

      }

    }

    print_r($serie);

  }

  public function actionVerifySourceProduct() {

    $source_products = \app\models\Toner\Source\Product::find()
      ->joinWith("sourceProductsJoin")
      ->andWhere(["source" => "tuttocartucce", "toner_source_product.disabled" => 0])
      ->andWhere(["toner_source_product_join.id_source_product" => null])
      ->all();

    foreach ($source_products as $source_product) {

      $this->getProbablyProduct($source_product);

    }

  }

  private function getProbablyProduct($source_product) {

    foreach ($source_product->modelli as $source_modello) {

      if ($source_modello->id_modello) {

        $modello = $source_modello->modello;

        $prodotti = $modello->products;

        foreach ($prodotti as $prodotto) {

          $c1 = strtoupper($source_product->color);
          $c2 = strtoupper($prodotto->colore);

          if ($c1 == $c2) {

            echo "[$source_product->sku] [$prodotto->sku]\n";

          }

        }

      }

    }

  }

  public function actionTest() {

    $_products = \app\models\Toner\Product::find()
      ->andWhere(["sku" => "IMEPNT2991"])
      //->limit(100)
      ->all();

    foreach ($_products as $_p) {

      $source = null;

      if ($_p->manuale)
        continue; // Prodotto aggiornato manualmente

      $prezzo_compatibile = 0;

      $_price = $_p->getPrezzoVS("OfferteCartucce.com");

      if ($_price) {
        $prezzo_compatibile = round($_price * 1.05, 6);
        $source = "offertecartucce.com VS";
        //echo $source."\n";
      }

      if ($prezzo_compatibile == 0) {

        $_price = $_p->getPrezzoVS("TuttoCartucce.com");

        if ($_price) {
          if ($_p->tipologia == "TONER")
            $prezzo_compatibile = round($_price * 0.95, 6);
          else
            $prezzo_compatibile = round($_price * 1.1, 6);

          $source = "tuttocartucce.com VS";
          //echo $source."\n";
        }

      }

      if ($prezzo_compatibile == 0) {

        $source = $_p->getSource("tuttocartucce");

        if ($source) {
          if ($_p->tipologia == "TONER")
            $prezzo_compatibile = round($source["price"] * 0.95, 6);
          else
            $prezzo_compatibile = round($source["price"] * 1.1, 6);

          $source = "tuttocartucce.com";
          //echo $source." [$_price->prezzo_avg]\n";
        }

      }

      if ($prezzo_compatibile == 0) {

        $source = $_p->getSource("puntorigenera");

        if ($source) {
          if ($_price > 2)
            $prezzo_compatibile = round($source["price"] * 2, 6);
          else
            $prezzo_compatibile = round($source["price"] * 6, 6);

          $source = "puntorigenera.it";
        }

      }

      if ($prezzo_compatibile == 0) {

        $_price = $_p->getPrezzoMedioVs();

        if ($_price) {
          $prezzo_compatibile = round($_price, 6);

          $source = "source verdestampa avg";
        }

      }

      if ($source) {
        $_p->compatibile_prezzo_source = $source;
        $_p->save();
      }

    }

  }

}

?>
