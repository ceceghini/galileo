<?php

namespace app\commands;

use yii\console\Controller;
use app\models\Toner\Marche;
use app\models\Toner\Serie;
use app\models\Toner\Modelli;
use app\models\Toner\Product;
use app\models\Toner\ProductModelli;
use app\models\Toner\Prezzi;
use app\models\Toner\MapTipologie;
use app\components\Proxy;
use app\components\Util;

class VerdestampaController extends Controller
{

  private $url = "https://www.stampante.com";

  private $marche;

  private $tipologie_scartate = array();

  private function loadMarche() {

    $marche = Marche::find()->all();
    foreach ($marche as $m) {
      $this->marche[$m->nome] = $m->id;
    }

  }



  private function processSite() {

    $dom = new \PHPHtmlParser\Dom;
    $dom->load($this->url);

    $values = $dom->find("select[name=brand] option");

    foreach($values as $value) {

      if ($value->value)
        $this->elaboraMarca($value->innerHtml, $value->value);

    }

  }

  /**
    Elaborazione di una marca
  **/

  private function elaboraMarca($marca, $id) {

    $marca = strtoupper($marca);

    $model = Marche::findOne(["nome" => $marca]);

    if (!$model) {
      $model = new Marche(["nome" => $marca]);
      //$model->url = $url;
      $model->save();
      echo "Marca salvata [$marca]\n";
    }

	}

  public function actionReset() {

    \Yii::$app->db->createCommand("update toner_serie set id_verdestampa = 0 where id_verdestampa > 0")->query();
    \Yii::$app->db->createCommand("update toner_modelli set in_verdestampa = 0")->query();

    \Yii::$app->db->createCommand("update toner_modelli set elaborato = 0")->query();
    \Yii::$app->db->createCommand("update toner_product set elaborato = 0, id_verdestampa = 0 where manuale = 0")->query();

  }

  /**
  Elaborazione delle marche
  */
  public function actionProcessBrand() {

    $_marche = Marche::find()
      ->all();

    foreach ($_marche as $key => $_marca) {

      $url = "$this->url/search?brand=".$_marca->id_verdestampa;

      Util::debug("Process marca [{$_marca->nome}]");

      $values = $this->getJson($url);

      foreach ($values as $value) {
        $this->elaboraSerie($_marca, $value["series"]);
		  }

    }

  }

  private function elaboraSerie($_marca, $serie) {

    if ($serie == "zzz")
      $serie = "ALTRI MODELLI";

    $serie = strtoupper($serie);
    $serie = str_replace($_marca->nome." ", "", $serie);

    $model = Serie::findOne(["nome" => $serie, "id_marca" => $_marca->id]);

    if (!$model) {
      $model = new Serie(["nome" => $serie]);
      $model->id_marca = $_marca->id;
      $model->save();

      if ($model->hasErrors())
        print_r($model->getErrors());
      else
        echo "Serie salvata [$_marca->nome] [$model->nome]\n";
    }

    $model->id_verdestampa = 1;
    $model->save();

  }

  /**
  Elaborazione delle serie
  */
  public function actionProcessSerie() {

    $_models = Serie::find()
      ->andWhere(["id_verdestampa" => 1])
      ->all();

    foreach ($_models as $key => $_serie) {

      $nomeSerie = $_serie->nome;
      if ($nomeSerie=="ALTRI MODELLI")
        $nomeSerie = "Altri modelli";

      Util::debug("Process serie [{$_serie->nome}]");

      $url = "$this->url/search?brand=".$_serie->marca->id_verdestampa."&series=" . str_replace(" ", "+", $nomeSerie);

      $values = $this->getJson($url);

      if (sizeof($values)==0 && $nomeSerie == "Altri modelli") {

        $nomeSerie = "zzz";

        $url = "$this->url/search?brand=".$_serie->marca->id_verdestampa."&series=" . str_replace(" ", "+", $nomeSerie);

        $values = $this->getJson($url);

      }

      if (sizeof($values)==0) {
        echo "Problema nella serie [{$_serie->marca->nome}] [$_serie->nome]\n";
        echo $url."\n";
      }

      foreach ($values as $value) {
        $this->elaboraModello($_serie->marca, $_serie, $value);
		  }

    }

  }

  private function elaboraModello($_marca, $_serie, $_modello) {

    $modello = strtoupper($_modello["model"]);
    $modello = str_replace($_serie->nome." ", "", $modello);
    $modello = str_replace($_serie->nome."-", "", $modello);

    $model = Modelli::findOne(["id_verdestampa" => $_modello["id"]]);

    if (!$model)
      $model = Modelli::findOne(["nome" => $modello, "id_serie" => $_serie->id]);

    if (!$model) {
      $model = new Modelli(["nome" => $modello]);
      $model->id_serie = $_serie->id;
      $model->serie = $_serie->nome;
      $model->marca = $_marca->nome;
      $model->id_verdestampa = $_modello["id"];

      $model->save();

      if ($model->hasErrors())
        print_r($model->getErrors());
      else
        echo "Modello salvato [$model->nome] [$model->serie] [$model->marca]\n";

    }
    else {
      $model->id_verdestampa = $_modello["id"];
    }

    if ($model->id_serie != $_serie->id) {
      echo "Modello [$model->nome] - serie modificata [$model->id_serie] [$_serie->id]\n";
      $model->id_serie = $_serie->id;
      $model->serie = $_serie->nome;
    }

    $model->in_verdestampa = 1;
    $model->save();

  }

  /* Elaborazione modelli da SQL */
  public function actionProcessModelli() {

    $_modelli = Modelli::find()
      ->andWhere([
        "elaborato" => 0,
//        "id" => 6696,
        "in_verdestampa" => 1
      ])
      ->all();

  	foreach ($_modelli as $_modello) {

      $this->processSingleModello($_modello);

  	}

    // verifica tipologie scartate
    if (sizeof($this->tipologie_scartate)>1) {
      echo "TIPOLOGIE SCARTATE\n";
      print_r($this->tipologie_scartate);
    }

  }

  public function actionProcessSingleModello() {

    $_modello = Modelli::findOne(2593);
    $this->processSingleModello($_modello);

  }

  private function processSingleModello($_modello) {

    $url = $this->url.'/redirect?why=search&page=printer&id='.$_modello->id_verdestampa;
    //echo $url."\n";
    $url2 = Proxy::redirectUrl($url);
    //echo $url2."\n";

    $html = $this->getHtml($url2);

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    if (!$_modello->photo) {

      $imgs = $dom->find("img[class=stampante]");

      if (sizeof($imgs)>0) {
        $src = $imgs[0]->src;
        if ($_modello->photo != "https://www.stampante.com".$src) {
          $_modello->photo = "https://www.stampante.com".$src;
          $_modello->save();
          echo "Immagine modello salvata [$_modello->nome] [$_modello->serie] [$_modello->nome]\n";
        }
      }
    }

    $values =  $dom->find("div[class=product-row]");

    foreach ($values as $product_row) {

      $item = $this->getItem($product_row);

      $_product = $this->elaboraProdotto($_modello, $item);

      if ($_product) {

        $rel = ProductModelli::findOne(["id_prodotto" => $_product->id, "id_modello" => $_modello->id] );

        if (!$rel) {
            $rel = new ProductModelli();
            $rel->id_prodotto = $_product->id;
            $rel->id_modello = $_modello->id;
            $rel->save();
            echo "Relazione inserita [$_product->sku] [$_modello->nome]\n";
        }

      }

    }

    $_modello->elaborato = 1;
    $_modello->save();

  }

  private function elaboraProdotto($_modello, $item) {

  	$id_verdestampa = $item["ID"];

    $_product = Product::findOne(["sku" => $item["CODICE"]]);

  	if (!$_product) {

      $_product = new Product();

      $_product->sku = $item["CODICE"];

      $result = \Yii::$app->db->createCommand("select tipologia from toner_map_tipologie where tipologia_vs = :t")
        ->bindValue(":t", $item["TIPO"])
        ->queryScalar();

      if (!$result) {
        $this->tipologie_scartate[$item["TIPO"]] = 1;
        return;
      }

      $_product->tipologia = $result;

      echo "Nuovo prodotto [{$item["CODICE"]}]\n";

  	}

    if (isset($item["COLORE"])) {
      if ($_product->colore != $item["COLORE"]) {
        //echo "COLORE [{$_product->colore}] [{$item["COLORE"]}]\n";
        $_product->colore = $item["COLORE"];
      }
    }

    if (isset($item["RESA"])) {
      if ($_product->resa != $item["RESA"]) {
        //echo "RESA [{$_product->resa}] [{$item["RESA"]}]\n";
        $_product->resa = $item["RESA"];
      }
    }

    $_product->id_verdestampa = $id_verdestampa;

    $_product->save();

    return $_product;

  }

  private function getItem($el) {

  	$item = array();

  	$item["ID"] = str_replace("riga-", "", $el->id);

    $data = $el->find("ul[class=proprieta-cartuccia] li");

    foreach ($data as $proprieta) {
      $text = strip_tags(trim($proprieta->innerHtml));
      $text = str_replace("Tipo", "Tipo:", $text);

      if ($text!="&nbsp;") {
          $values = explode(":", $text);
          $item[strtoupper(trim($values[0]))] = strtoupper(trim($values[1]));
      }

    }

    $data = $el->find("div div div a");

    $item["URL"] = $this->url.$data->href;
    if ($item["URL"]=="https://www.stampante.com#")
      $item["URL"] = "";

  	return $item;

  }

  public function actionProcessProduct() {

    $this->loadMarche();

    $this->processProductWithoutBrand();

    $this->processProduct();

  }

  private function processProductWithoutBrand() {

    $products = Product::find()
      ->andWhere(["id_marca"=>null])
      ->andWhere(["not", ["id_verdestampa" => 0]])
      //->andWhere(["sku"=>'K96/01A'])
      ->all();

    foreach ($products as $product) {
      $this->elaboraProdotto2($product);

    }

  }

  private function processProduct() {

    $products = Product::find()
      ->andWhere(["manuale"=>0])
      ->andWhere(["elaborato"=>0])
      ->andWhere(["not", ["id_verdestampa" => 0]])
      ->andWhere(["not", ["tipologia" => null]])
      ->all();

    //$atipo = ["CARTUCCIA NERO", "TONER NERO", "TONER CIANO", "TONER MAGENTA"];

    foreach ($products as $product) {

      $this->elaboraProdotto2($product);

    }

  }

  private function elaboraProdotto2($product) {

    $url_r = $this->url.'/redirect?why=search&page=cartridge&id='.$product->id_verdestampa;
    //echo $url_r."\n";
    $url = Proxy::redirectUrl($url_r);

    $html = $this->getHtml($url);

    if (!$html)
      return;

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    // Recupero Marca
    $values = $dom->find("h1");
    $id_marca = $this->getMarca($values->innerHtml);

    if ($product->id_marca != $id_marca) {
      echo "[$product->sku] Marca modificata [$product->id_marca] [$id_marca]\n";
      $product->id_marca = $id_marca;
      $product->save();
    }

    // Recupero part_number
    $values = $dom->find("table[class=table-striped] tbody tr");

    foreach ($values as $value) {
      $tds = $value->find("td");
      if (strtolower(trim($tds[0]->innerHtml))=="part number") {

        $part_number = "#".str_replace(" , ", "#", trim($tds[1]->innerHtml))."#";
        if ($product->part_number != $part_number) {
          echo "[$product->sku] Part number modificato [$product->part_number] [$part_number]\n";
          $product->part_number = $part_number;
          if(!$product->save())
            print_r($product->getErrors());
        }

      }

    }

    // Recupero foto
    if (!$product->originale_url_foto) {
      $img = $dom->find("img[class=cartuccia2]");

      if (sizeof($img)>0) {

        $product->originale_url_foto = $this->url."/".$img[0]->src;
        $product->save();
        echo "[$product->sku] Foto originale salvata\n";
      }

    }

    $product->url = $url;

    $product->elaborato = 1;
    if(!$product->save()) {
      print_r($product->getErrors());
    }

  }

  private function getMarca($title) {

    foreach ($this->marche as $value=>$id) {
      if (strpos(strtoupper($title)," ".strtoupper($value)." ")!==false)
        return $id;
    }
    return null;

  }

  public function actionDownloadPhoto() {

    $_modelli = \app\models\Toner\Modelli::find()
      ->andWhere(["photo" => null])
      ->all();

    foreach ($_modelli as $_modello) {

      $url = $this->url.'/redirect?why=search&page=printer&id='.$_modello->id_verdestampa;
      //echo $url."\n";
      $url2 = Proxy::redirectUrl($url);
      //echo $url2."\n";
      $html = $this->getHtml($url2);

      $dom = new \PHPHtmlParser\Dom;
      $dom->loadStr($html, []);

      $imgs = $dom->find("img[class=stampante]");

      if (sizeof($imgs)>0) {
        $src = $imgs[0]->src;
        if ($_modello->photo != "https://www.stampante.com".$src) {
          $_modello->photo = "https://www.stampante.com".$src;
          $_modello->save();
          echo "Immagine modello salvata [$_modello->nome] [$_modello->serie] [$_modello->nome]\n";
          //$_modello->downloadPhoto();
        }
      }

    }

  }

  public function getJson($url) {

		$proxy = new Proxy();

		$headers = [
      'Host: www.stampante.com',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0',
      'Accept: application/json, text/javascript, */*; q=0.01',
      'Accept-Language: en-US,en;q=0.5',
      'Accept-Encoding: gzip, deflate, br ',
      'Referer: https://www.stampante.com/',
      'X-Requested-With: XMLHttpRequest',
      'Connection: keep-alive',
      'Pragma: no-cache',
      'Cache-Control: no-cache'
    ];

		$proxy->setopt(CURLOPT_HTTPHEADER, $headers);

		return json_decode($proxy->curl($url), true);

	}

  public function getHtml2($url) {

		$proxy = new Proxy();

		$headers = [
      'Host: www.stampante.com',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0',
//      'Accept: application/json, text/javascript, */*; q=0.01',
//      'Accept-Language: en-US,en;q=0.5',
//      'Accept-Encoding: gzip, deflate, br ',
      'Referer: https://www.stampante.com/',
      'X-Requested-With: XMLHttpRequest',
      'Connection: keep-alive',
      'Pragma: no-cache',
//      'Cache-Control: no-cache',
    ];

    $headers = [
      "Host: www.stampante.com",
      "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0",
      "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
      "Accept-Language: en-US,en;q=0.5",
      "Accept-Encoding: gzip, deflate, br",
      //"Cookie: screen=1920x1200; _ga=GA1.2.479471805.1524813906; _gid=GA1.2.2040125347.1524813906; _gat=1",
      "Connection: keep-alive",
      "Upgrade-Insecure-Requests: 1",
      "Pragma: no-cache",
      "Cache-Control: no-cache"
    ];

		$proxy->setopt(CURLOPT_HTTPHEADER, $headers);

    return $proxy->curl($url);

	}

  public function getHtml3($url) {

		$proxy = new Proxy();

		$headers = [
      'Host: www.stampante.com',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
      'Accept: */*',
      'Accept-Language: en-US,en;q=0.5',
      //'Accept-Encoding: gzip, deflate, br',
      'Referer: https://www.stampante.com/',
      'X-Requested-With: XMLHttpRequest',
      'Connection: keep-alive',
      'Pragma: no-cache',
      'Cache-Control: no-cache',
    ];

    $proxy->setopt(CURLOPT_HTTPHEADER, $headers);

    return $proxy->curl($url);

	}

  public function actionProcessPrezzi() {

    $_products = Product::find()
      ->andWhere([">", "id_verdestampa", 0])
      ->all();

    foreach ($_products as $_product) {
      $this->processPrezzi($_product);
    }

  }

  public function actionProcessPrezzi2() {

    $_products = Product::find()
      ->andWhere([">", "id_verdestampa", 0])
      //->andWhere(["sku" => "TN-2310"])
      ->andWhere("not exists (select 1 from toner_product_prezzi r where r.id_product = toner_product.id)")
      ->all();

    foreach ($_products as $_product) {
      //echo "$_product->sku\n";
      $this->processPrezzi($_product);
    }

  }

  private function processPrezzi($_product) {

    $url = $this->url."/offerte?id={$_product->id_verdestampa}&tipo=compatibile";

    $html = $this->getHtml3($url);

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $offerte = $dom->find("div[class=row]");

    if (sizeof($offerte)==0) {
      //echo "Nessuna offerta trovata [$_product->sku]\n";
      return;
    }

    \Yii::$app->db->createCommand()->delete("toner_product_prezzi", "id_product = $_product->id")->execute();

    //echo $url."\n";

    foreach ($offerte as $offerta) {

      $negozio = $offerta->find("div a[class=styleColor]");
      $prezzo = $offerta->find("div[class=colprezzo] span[class=spanprezzo]");

      if (sizeof($negozio)==0) {
        continue;
      }

      $nomenegozio = (string) trim($negozio->innerHtml);

      //print $nomenegozio."\n";

      if (
        $nomenegozio != "Stampaperfetta.it" &&
        $nomenegozio != "Tonerpertutti.it" &&
        $nomenegozio != "Ecolors.it"
        ) {

        $prezzo = str_replace("&euro;", "", $prezzo->innerHtml);

        \Yii::$app->db->createCommand()->insert("toner_product_prezzi", [
          "id_product" => $_product->id,
          "negozio" => $nomenegozio,
          "prezzo" => $prezzo / 1.22
          ])->execute();

        $n = \Yii::$app->db->createCommand("select count(*) from toner_product_prezzi_storico where id_product = :id_product and negozio = :negozio and validita = :validita")
          ->bindValue(":id_product", $_product->id)
          ->bindValue(":negozio", $nomenegozio)
          ->bindValue(":validita", date("Y-m-d"))
          ->queryScalar();

        if ($n=00)
          \Yii::$app->db->createCommand()->insert("toner_product_prezzi_storico", [
            "id_product" => $_product->id,
            "negozio" => $nomenegozio,
            "prezzo" => $prezzo / 1.22,
            "validita" => date("Y-m-d")
            ])->execute();

      }


    }

  }

  private function getHtml($url) {

    $proxy = new Proxy();
    $proxy->setopt(CURLOPT_HEADER, true);

    $html = $proxy->curl($url);

    if (strpos($html, "HTTP/1.1 404 Not Found")!==false)
      return null;
    else
      return $html;

  }

}

 ?>
