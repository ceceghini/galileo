<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use Sendinblue\Mailin;
use app\components\Util;

class NewsletterController extends Controller {

  private $email = array();
  private $mailin;
  private $shop_map = [
    "lacartucciacompatibile.it" => "stampaperfetta.it",
    "ecolors.it" => "stampaperfetta.it"
  ];
  private $template = [
    "stampaperfetta.it" => 33,
    "tonerpertutti.it" => 43
  ];

  public function actionSync() {

    $this->mailin = new Mailin("https://api.sendinblue.com/v2.0","k0wNOB97VUPsh8Tn");

    $this->syncShop("https://www.ecolors.it/pointec5521xy/feed/newsletter.php", 6);
    $this->syncShop("https://www.tonerpertutti.it/pointec5521xy/feed/newsletter.php", 4);
    $this->syncShop("https://www.stampaperfetta.it/stampape/galileo/newsletter", 2);

  }

  private function syncShop($source, $list) {

    $emails = Util::curlJson($source);

    foreach ($emails as $value) {

      $data = [
        "email" => $value["email"],
        "attributes" => [
          "NAME" => $value["firstname"],
          "SURNAME" => $value["lastname"],
          "NOME" => $value["firstname"]." ".$value["lastname"]
        ],
        "listid" => [
          $list
        ]
      ];

      $result = $this->mailin->create_update_user($data);

      if (isset($result["code"])) {
        if ($result["code"]!="success")
          print_r($result);
      }

    }

  }

  public function actionSendClient() {

    $_products = \Yii::$app->dbOdoo->createCommand("
    select REPLACE(p.default_code, 'TO/', 'TC/') as sku, sum(product_uom_qty) as qty
      from sale_report s
        join product_product p on s.product_id = p.id
       where (p.default_code like 'TC/%' or p.default_code like 'TO/%')
         and state = 'confirmed'
         and s.date > (current_date - INTERVAL '36 months')
        group by REPLACE(p.default_code, 'TO/', 'TC/')
        having sum(product_uom_qty) > 10
        order by random()
        limit 1")->queryAll();

    foreach ($_products as $_product) {

      $this->processProduct($_product);

    }

    //print_r($this->email);

    $this->mailin = new Mailin("https://api.sendinblue.com/v2.0","k0wNOB97VUPsh8Tn");

    foreach ($this->email as $shop => $products) {
      //print $shop."\n";
      foreach ($products as $sku => $emails) {

        $product_info = $this->getProductInfo($shop, $sku);

        foreach ($emails as $email) {
          $this->sendEmail($shop, $product_info, $email);
        }

      }
    }

  }

  private function getProductInfo($shop, $sku) {

    if ($shop=="stampaperfetta.it")
      $url = "https://www.$shop/stampape/galileo/productinfo?sku=$sku";
    else
      $url = "https://www.$shop/feed/galileo/product_info.php?sku=$sku";

    $data = file_get_contents($url);

    return json_decode($data, true);

  }

  private function sendEmail($shop, $product_info, $email) {

    $data = [
      "id" => $this->template[$shop],
      "to" => $email,
      "replyto" => "servizioclienti@$shop",
      "attr" => [
        "PRODUCT_NAME" => $product_info["name"],
        "PRICE" => $product_info["price"],
        "SPECIAL_PRICE" => $product_info["special_price"],
        "PRODUCT_URL" => $product_info["url"]."?utm_source=newsletter&utm_medium=email&utm_campaign=prodottivenduti",
        "IMAGE" => $product_info["image"]
      ],
    ];

    $result = $this->mailin->send_transactional_template($data);

    if (isset($result["code"])) {
      if ($result["code"]=="success")
        return;
    }

  }

  private function processProduct($_product) {

    $sku = $_product["sku"];
    $sku2 = str_replace("TC/", "TO/", $_product["sku"]);

    $_partners = \Yii::$app->dbOdoo->createCommand("
    select distinct lower(b.email) as email, sh.name as shop_name
  from sale_report s
    join product_product p on s.product_id = p.id
    join res_partner b on s.partner_id = b.id
    join sale_shop sh on s.shop_id = sh.id
   where (p.default_code like 'TC/%' or p.default_code like 'TO/%')
     and state = 'confirmed'
     and s.date > (current_date - INTERVAL '36 months')
     and p.default_code in ('$sku', '$sku2')
     and b.email is not null")->queryAll();

    foreach ($_partners as $_partner) {

      $shop = $_partner["shop_name"];
      if (isset($this->shop_map[$shop]))
        $shop = $this->shop_map[$shop];

      $this->email[$shop][$sku][] = $_partner["email"];

    }

  }

}

?>
