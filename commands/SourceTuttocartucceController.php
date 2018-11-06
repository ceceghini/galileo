<?php

namespace app\commands;
use app\models\Toner\Source\Product;

class SourceTuttocartucceController extends SourceController
{

  private $url = "https://www.tuttocartucce.com/";
  protected $source = "tuttocartucce";

  protected $marche_alias = [
    "KYOCERA" => "KYOCERA MITA"
  ];

  // Elaborazione pagina principale
  function processMain() {

    $dom = new \PHPHtmlParser\Dom;
    $html = $this->getHtml($this->url);
    $dom->loadStr($html, []);

    $values = $dom->find("select[id=ctl00_ContentPlaceHolder__PrinterSearch_ddlMake] option");

    foreach ($values as $value) {

      if ($value->value=="Seleziona la marca"||$value->value=="-1")
        continue;

      $this->elaboraMarca($value->innerHtml, $value->value);

    }

  }

  function processBrandSingle($_marca) {

    $request_body = '{"knownCategoryValues":"undefined:'.$_marca->source_key.';","category":"Serie"}';
    $values = $this->getJson($this->url."/wsPrinters.asmx/GetSeries", $request_body);
    foreach ($values["d"] as $value) {
      //echo $value["name"]."\n";
      if ($value["name"]=="")
        continue;
      $this->elaboraSerie($_marca, $value["name"], $value['value']);
    }

  }

  function processSerieSingle($_serie) {

    $request_body = '{"knownCategoryValues":"undefined:'.$_serie->sourceMarca->source_key.';Serie:'.$_serie->source_key.';","category":"Printer"}';
    $values = $this->getJson($this->url."/wsPrinters.asmx/GetPrinters", $request_body);
    foreach ($values["d"] as $value) {
      if ($value["name"]=="")
        continue;
      //echo $value["name"]."\n";
      //echo "-".$value['value']."\n";
      $this->elaboraModello($_serie->sourceMarca, $_serie, $value["name"], $value['value'], "");
    }

  }

  function processModelliSingle($_modello) {

    $url = $this->url."Prodotti.aspx?IDPrn=".$_modello->source_key;

    //echo $url." ...";

    $html = $this->getHtml($url);

    //print $html;

    if ($html == "")
      return true;

    if (strpos($html, "Nessun prodotto disponibile per la categoria selezionata")!==false) {
      return true;
    }

    //print $html;

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);
    $values = $dom->find("div[class=prodotto_interno_divisione_categoria]");

    $elaborato = false;
    foreach ($values as $value) {
      //echo "a";
      $elaborato = true;
      // Recupero l'url
      $a = $value->find("span[itemprop=name] a");

      // Verifico se si tratta di un prodotto originale
      $descrizioneProdo = $value->find("div[class=descrizioneProdo]");
      $descrizione = strtoupper($descrizioneProdo->innerHtml);

      if (strpos($descrizione, "ORIGINAL")!==false)
        continue;

      $urlP = $this->url.htmlspecialchars_decode($a->href);

      $data = explode("/", $urlP);

      if ($data[5]!="Toner" && $data[5]!="Cartucce") {
        if ($data[5]!="Inchiostri" && $data[5]!="Carta" && $data[5]!="Consumabili-Per-Etichettatrici")
          print_r($data);
        continue;
      }

      // Verifica le categorie
      //$parts = parse_url($urlP);
      //parse_str($parts['query'], $query);

      //if ($query["IDCat"]!=255 && $query["IDCat"]!=245 && $query["IDCat"] != "" && $query["IDCat"]!=257)
      //  continue;

      //$urlP = $this->url."ProdottiDettaglio.aspx?IDItem=".$query["IDItem"];

      $this->elaboraProdotto($urlP, $data["10"], $_modello);

    }

    //echo $elaborato."\n";

    return $elaborato;

  }

  function processUrlSingle($_url) {

    return true;

  }

  public function actionProcessPrice() {

    $_products = Product::find()
      ->andWhere([
        "source" => $this->source,
      ])
      ->all();

    foreach ($_products as $_source) {

      $check = false;

      foreach ($_source->products as $_product) {

        $_price = $_product->getPrezzoOfferteCartucce();
        if ($_price)
          continue;

        $_price = $_product->getPrezzoTuttoCartucceVs();
        if ($_price)
          continue;

        $check = true;

      }

      if ($check) {
        $this->processProductSingle($_source);
      }

    }

  }

  function processProductSingle($_product) {

    $url = $_product->url;

    $html = $this->getHtml($url);
    if ($html == "")
      return;

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);
    $data = $dom->find("div[class=center_cube_white_title ]");
    if (sizeof($data)==0) {
      $_product->is_present = 0;
      $_product->save();
      return;
    }

    $_product->sku = $this->getItem($dom, "- Codice originale:");
    $_product->color = $this->getItem($dom, "- Colore:");
    $_price = $dom->find("span[itemprop=price]");
    $price = str_replace(",", ".", str_replace("&euro; ", "", $_price->innerHtml));
    $_product->price = round($price/1.22, 1);
    $title = $dom->find("div[itemprop=name]");
    $_product->title = strtoupper($title->innerHtml);
    $description = $dom->find("span[itemprop=description]");
    $_product->description = strtoupper($description->innerHtml);
    $_product->html = $html;
    //$_product->url = $url;

    $this->updateProduct($_product);

  }

  private function getItem($dom, $key) {

    $values = $dom->find("div[class=caratteristiche_prodotto] div[class=caratteristiche_prodotto_left] table tr");
    //print $values->innerHtml."\n";
    foreach ($values as $value) {
      $td = $value->find("td");
      if ($td[0]->innerHtml == $key) {
          return $td[1]->innerHtml;
      }
    }

  }

  function disableProduct() {

    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%OFFERTA%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and sku is null")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '% 2 PEZZI%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%DI ALTA QUALITA%'")->execute();

  }

  private function getHtml($url) {

    $proxy = new \app\components\Proxy();

    $headers = [
      'Host: www.tuttocartucce.com',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0',
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language: en-US,en;q=0.5',
//      'Accept-Encoding: gzip, deflate, br ',
      'Cookie: _ga=GA1.2.111004571.1511368177; ASP.NET_SessionId=4xmqub1zhcmn4hjpxalamg1k; _gid=GA1.2.67282278.1511794348; _gat_testAB=1; _gat=1',
      'Connection: keep-alive', //Your referrer address
    ];

    $proxy->setopt(CURLOPT_HTTPHEADER, $headers);

    return $proxy->curl($url);

  }

  private function getJson($url, $request_body) {

    $proxy = new \app\components\Proxy();

    $proxy->setopt(CURLOPT_POSTFIELDS, $request_body);
    $proxy->setopt(CURLOPT_HTTPHEADER, array(
          'Host: www.tuttocartucce.com',
          //'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0',
          'Accept: */*',
          'Accept-Language: en-US,en;q=0.5',
          'Accept-Encoding: gzip, deflate, br',
          //'Referer: https://www.tuttocartucce.com/',
          'X-Requested-With: XMLHttpRequest',
          'Content-Type: application/json; charset=utf-8',
          'Content-Length: ' . strlen($request_body)),
          'Connection: keep-alive',
          'TE: Trailers'
      );

    return json_decode($proxy->curl($url), true);

  }

  public function actionTest() {

    $request_body = '{"knownCategoryValues":"undefined:82;","category":"Serie"}';
    $headers = [
      "Host: www.tuttocartucce.com",
      "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0",
      "Accept: */*",
      "Accept-Language: en-US,en;q=0.5",
      "Accept-Encoding: gzip, deflate, br",
      "Referer: https://www.tuttocartucce.com/",
      "X-Requested-With: XMLHttpRequest",
      "Content-Type: application/json; charset=utf-8",
      "Content-Length: 58",
      //"Cookie: _ga=GA1.2.2047023639.1530101683; ssupp.vid=O7RWTphc1fozmmFnDlu2tlzlG0YkMncmYp43141427062018; SL_C_23361dd035530_VID=aAQAsx2ty7w_; SL_C_23361dd035530_KEY=aa99d82fed17e617680550466c8bdb87eecd4fef; SL_C_23361dd035530_SID=puFi7s4eyOu_; displayCookieConsent=y; ASP.NET_SessionId=2uvxnekjak1pmz5aqylsktzv; _gid=GA1.2.1860805336.1530610340; _gat=1; ssupp.visits=3; ssupp.animbnr=false; ssupp.chatid=8wEBapwq4jCWX4MWqVzZ4qDnV9WdaZTx",
      "Connection: keep-alive",
      "TE: Trailers",
    ];
    $url = 'https://www.tuttocartucce.com/wsPrinters.asmx/GetSeries';

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec( $ch );
    curl_close( $ch );

    print $data;

  }

}
