<?php

namespace app\components\Odoo;

use app\components\OdooClient as Client;

class Product {

  private $client;

  function __construct() {

    $this->client = new Client();

  }

  public function syncPrice() {

    // Recupero prodotti compatibili
    $criteria = [
      ['default_code', 'like', "TO/"],
    ];
    $field = [
      "default_code",
      "standard_price"
    ];

    $products = $this->client->search_read('product.product', $criteria, $field, 0);

    $this->syncProductsTO($products, "TO/");

    $criteria = [
      ['default_code', 'like', "TC/"],
    ];
    $field = [
      "default_code",
      "standard_price"
    ];

    $products = $this->client->search_read('product.product', $criteria, $field, 0);

    $this->syncProductsTC($products, "TC/");

  }

  public function syncOrderPrice() {

    $orders = $this->client->search('sale.order', [["date_order", ">=", "2018-01-01"]], 0);

    foreach ($orders as $order) {

      $order_lines = $this->client->search_read('sale.order.line', [["order_id", "=", $order]], ["product_id", "purchase_price"], 0);

      foreach ($order_lines as $line) {

        $purchase_price = 0;

        if ($line["product_id"][0]==$this->product_shipping) {

          $_order = $this->client->read('sale.order', [$order], ["payment_method_id", "client_order_ref"]);

          if ($_order[0]["client_order_ref"]=="originale" || $_order[0]["client_order_ref"]=="misto")
            $purchase_price = 6.9;
          else
            $purchase_price = 2.87;

          // Spese di spedizione
          if ($_order[0]["payment_method_id"][0]==1)
            $purchase_price += 3.5;

        }
        elseif ($line["product_id"][0]==$this->product_cashondelivery) {
          $purchase_price = 3.5;
        }
        else {
          $product = $this->client->search_read('product.product', [["id", "=", $line["product_id"][0]]], ["standard_price"], 0);
          $purchase_price = $product[0]["standard_price"];
        }

        //if ($line["purchase_price"]==0) {

          $product = $this->client->search_read('product.product', [["id", "=", $line["product_id"][0]]], ["standard_price"], 0);

          $data = [
            "purchase_price" => $purchase_price
          ];

          $this->client->write('sale.order.line', $line["id"], $data);

          echo "Prezzo line ordine aggiornato [{$line["id"]}] [$purchase_price] \n";

        }

      //}

    }

  }

  private function syncProductsTC($products, $prefix) {

    foreach ($products as $product) {

      $sku = str_replace($prefix, "", $product["default_code"]);

      $_product = \app\models\Toner\Product::findOne(["sku" => $sku]);

      if ($_product) {

        $source = $_p->getSource("puntorigenera");

        if ($source) {

          $prezzo = (float) round($source["price"], 2);
          $standard_price = (float)$product["standard_price"];

          if ("$prezzo" <> "$standard_price") {

            $data = [
              "standard_price" => $prezzo
            ];

            $this->client->write('product.product', $product["id"], $data);

            //echo "Costo puntorigenera modificato [{$product["default_code"]}] [{$product["standard_price"]}] => [$prezzo]\n";

          }

        }

      }

    }

  }

  private function syncProductsTO($products, $prefix) {

    foreach ($products as $product) {

      $sku = str_replace($prefix, "", $product["default_code"]);

      $_product = \app\models\Toner\Product::findOne(["sku" => $sku]);

      if ($_product) {

        $source = $_product->getSource("supplies24");

        if ($source) {

          $prezzo = (float) round($source["price"], 2);
          $standard_price = (float)$product["standard_price"];

          if ("$prezzo" <> "$standard_price") {

            $data = [
              "standard_price" => $prezzo
            ];

            $this->client->write('product.product', $product["id"], $data);

            //echo "Costo supplies24 modificato [{$product["default_code"]}] [{$product["standard_price"]}] => [$prezzo]\n";

          }

        }

      }

    }

  }

}
