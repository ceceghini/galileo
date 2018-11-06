<?php

namespace app\commands;

use app\components\Proxy;
use app\components\Util;

class SourcePuntorigeneraController extends SourceController
{

  private $url = "https://puntorigenera.com/multishop/";

  protected $source = "puntorigenera";

  protected $marche_alias = [
    "KYOCERA" => "KYOCERA MITA",
    "KYOCERA-MITA" => "KYOCERA MITA",
  ];

  private $url_elaborate;

  // Elaborazione pagina principale
  function processMain() {

    $dom = new \PHPHtmlParser\Dom;
    $html = $this->getHtml($this->url);
    $dom->loadStr($html, []);
    $values = $dom->find("select[id=lev0_1] option");

    foreach ($values as $value) {

      if ($value->value=="0")
        continue;

      $tmp = explode("|", $value->value);
      if (!isset($tmp[1]))
        continue;

      $tmp = explode("=", $tmp[1]);

      $nome_marca = $this->decodeMarca($value->innerHtml);

      $this->elaboraMarca($this->decodeMarca($value->innerHtml), $tmp[1], true);
    }

  }

  function processBrandSingle($_marca) {

    $url = $this->url."modules/categoriesnc/ajax.php?catID=$_marca->source_key&id_lang=1";

    $dom = new \PHPHtmlParser\Dom;
    $html = $this->getHtml($url);
    $dom->loadStr($html, []);

    $values = $dom->find("option");

    if (sizeof($values) == 0)
      return;

    foreach ($values as $value) {

      // Se il modello non ha prodotti non si fa niente
      $nome_modello = $value->innerHtml();
      $n = strpos($nome_modello, "(");

      if ($n===false)
        continue;

      // Se il modello ha 0 prodotto non si fa niente
      $m = strpos($nome_modello, ")");
      $i = substr($nome_modello, $n+1, $m - $n - 1);

      if ($i == 0)
        continue;

      // Salvo l'url del modello
      $this->elaboraUrl($value->value);

    }

  }

  function processSerieSingle($_serie) {

    return;

  }

  function processModelliSingle($_modello) {

    return;

  }

  function processUrlSingle($_url) {

    $url = $_url->url."?n=99";

    $html = $this->getHtml($url);

    if ($html == "")
      return false;

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $values = $dom->find("h5[class=product-name] a");

    foreach ($values as $value) {
      //echo $value->href."\n";
      $this->elaboraProdotto($value->href, 0);

    }

    return true;

  }

  function processProductSingle($_product) {

    //echo $_product->url."\n";

    $html = $this->getHtml($_product->url);
    if ($html == "")
      return;

    if (strpos($html, "Prodotto non trovato")!==false) {
      return false;
    }

    if (strpos($html, "Il prodotto non &egrave; disponibile.")!==false) {
      return false;
    }

    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);
    $html_product = $dom->find("div[itemtype='https://schema.org/Product']");

    $name = $dom->find("h1[itemprop=name]");
    $sku = $dom->find("span[itemprop=sku]");
    $description = $dom->find("div[itemprop=description]");
    $price = $dom->find("span[itemprop=price]");
    $our_price = $dom->find("p[class=our_price_display]");
    $tax = false;
    if (strpos($our_price->innerHtml, "tasse incl.")!==false) {
      $tax = true;
    }

    $_product->html = $html_product->innerHtml;
    $_product->title = trim($name->innerHtml);
    $_product->source_key = trim($sku->innerHtml);
    $_product->description = strip_tags($description->innerHtml);
    $_product->price = Util::getCostoPuntorigenera($price->content, $tax);

    $this->updateProduct($_product);

  }

  function disableProduct() {

    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%VUOTE%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%VUOTA%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%ORIGINALE %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like 'ETICHETTE %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like 'KIT %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like 'CHIP RESETTER PER %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%*SERIE ECO*%'")->execute();
    //\Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and name like '% CARTUCCE %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like 'LIQUIDO PULISCI TESTINE %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like 'VASCHETTA RECUPERO %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '% 2 PEZZI%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%10 CARTUCCE%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and title like '%CASSETTA NASTRO %'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and source_key like '%+%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and source_key like '10PZ%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and source_key like '5PZ%'")->execute();
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and source_key like 'CHIP%'")->execute();

  }

  // decodifica del nome della marca
  private function decodeMarca($marca) {

    $n = strpos($marca, "(");
    return substr($marca, 0, $n - 1);

  }

  // Recupera l'html tramite proxy
  function getHtml($url) {

    $proxy = new Proxy();

    $headers = [
      'Host: puntorigenera.com',
      'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0',
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language: en-US,en;q=0.5',
      'Cookie: PrestaShop-2a6ef0ed6074366cafbc30c373779aeb=d1lYlP3NaFLrkPvNNX57ZicqMsikMh35H6imb5ven%2FJ5m4kKY99YqPhvlOs%2F0t3mpinuIrLySspGvEOJ%2BvU96PSfODwhzw0wM3ryuLiZRus%3D000079; PrestaShop-d9695afc6523bc759f7c3336f148e104=d1lYlP3NaFLrkPvNNX57ZicqMsikMh35H6imb5ven%2FJRecbHrE4rDxmGlaZMbXy8BwdbEeVwd%2B3eO98UtG6lvFfrF1mFJFjXf3eOFlMvA6lLkDqwHrsqV6%2BfM%2Bl9c6pOI%2FcKw4fmnuH6asFCsIjS%2Bb8mVu01FLOi%2B9kIupVxiQGv%2FK5YpqhSryxmas%2FY1PBfccmsB7La89XYhe7TwMsCKAgZ3rACoV1S09dU2anDB4GqEB3Pv0WVIoBcXW6sQ4FT000186; _ga=GA1.2.113801221.1488470637; ssupp.vid=WLNKKobsRUJRe23G0lXo1ZT1ziunuYTzsf54451428112017; _gid=GA1.2.1290877627.1516111503; ssupp.geoloc=%7B%22ipAddress%22%3A%2278.134.93.175%22%2C%22countryCode%22%3A%22IT%22%2C%22country%22%3A%22Italy%22%2C%22region%22%3A%2209%22%2C%22city%22%3A%22Toscolano%20Maderno%22%7D; ssupp.chatid=qmu2Zt59qZdRjcfJRMY4CCAnHRNDKynI',
      'Connection: keep-alive',
      'Upgrade-Insecure-Requests: 1',
      'Pragma: no-cache',
      'Cache-Control: no-cache'
    ];

    $proxy->setopt(CURLOPT_HTTPHEADER, $headers);

    return $proxy->curl($url);

  }

}

?>
