<?php

namespace app\components\Odoo;

use app\components\Util;
use app\components\OdooClient as Client;

class ImportOrder {

  private $client;

  // Elenco negozi
  private $shop = [
    "stampaperfetta.it" => 1,
    "tonerpertutti.it" => 2,
    "ecolors.it" => 3,
    "lacartucciacompatibile.it" => 4
  ];

  // Mappatura metodi di pagamento
  private $payment_method = [
    "cashondelivery" => 1,
    "paypal_standard" => 3,
    "payplug" => 4,
    "bankpayment" => 2,
    "banktransfer" => 2,
  ];

  // Mappatura termini di pagamento
  private $payment_term = [
    "cashondelivery" => 4,
    "paypal_standard" => 5,
    "payplug" => 5,
    "bankpayment" => 5,
    "banktransfer" => 5,
  ];

  // Aziende generiche da concatenare col nome
  private $company = array (
    "AVVOCATO", "STUDIO TECNICO", "STUDIO LEGALE", "STUDIO MEDICO", "GEOMETRA", "STUDIO COMMERCIALE",
    "AMBULATORIO MEDICO", "ARCHITETTO", "STUDIO DI GEOLOGIA", "FARMACIA", "STUDIO DI ARCHITETTURA",
    "STUDIO PROFESSIONALE", "LIBERO PROFESSIONISTA", "MEDICO", "STUDIO", "STUDIO ARCHITETTURA", "INGEGNERE EDILE"
  );

  private $category_originale = 6;      // Category per toner originale
  private $category_compatibile = 5;    // Category per toner compatibile

  private $product_shipping = 6;        // Prodotto per spese di spedizione
  private $product_cashondelivery = 7;  // Prodotto per spese di incasso
  private $product_discount = 8;        // Prodotto per sconto

  private $shop_url;

  private $date_order;

  private $insert = true;

  function __construct() {

    $this->client = new Client();

  }

  public function import() {

    $this->importShop("stampaperfetta.it", "magento2");
    $this->importShop("tonerpertutti.it");
    $this->importShop("lacartucciacompatibile.it");
    $this->importShop("ecolors.it");

  }

  public function importTest() {

    $this->client = new Client(true);

    //$this->insert = false;

    $shop = "stampaperfetta.it";

    $source = "https://www.$shop/stampape/galileo/ordinitest";

    $ordini = Util::curlJson($source);

    $shop_id = $this->shop[$shop];

    foreach ($ordini as $ordine) {

      $this->importSingle($ordine, $shop_id);

    }

  }

  public function setOrigin() {

    $criteria = [
      ['origin', '=', False],
    ];

    $fields = ["id"];

    $orders = $this->client->search_read('sale.order', $criteria, $fields, 100);

    foreach ($orders as $order) {

      $type = array();

      $criteria = [
        ['order_id', '=', $order["id"]],
      ];
      $fields = [
        "product_id"
      ];

      $lines = $this->client->search_read('sale.order.line', $criteria, $fields, 100);

      foreach ($lines as $line) {

        $product = $this->client->read("product.product", $line["product_id"][0], ["default_code", "name"]);

        if ($product["default_code"])
          $prefix = substr($product["default_code"], 0, 2);

        $type[$prefix] = 1;

      }

      if (sizeof($type)==1) {
        $type = array_keys($type)[0];
        if ($type=="TC")
          $this->client->write('sale.order', $order["id"], ["origin" => "compatibile"]);
        if ($type=="TO")
          $this->client->write('sale.order', $order["id"], ["origin" => "originale"]);
      }
      else {
        $this->client->write('sale.order', $order["id"], ["origin" => "misto"]);
      }

    }

  }

  private function importShop($shop, $type="") {

    $this->shop_url = $shop;

    // Recupero degli ordini da completare in formato json
    if ($type=="magento2")
      $source = "https://www.$shop/stampape/galileo/ordini";
    else
      $source = "https://www.$shop/feed/galileo/ordini.php";

    $ordini = Util::curlJson($source);

    $shop_id = $this->shop[$shop];

    if (sizeof($ordini)==0)
      return;

    // Loop fra tutti gli ordini
    foreach ($ordini as $ordine) {

      // Verifico se l'ordine esiste già
      $criteria = [
        ['name', '=', $ordine["order_id"]],
      ];

      //print_r($criteria);

      $id = $this->client->searchOne('sale.order', $criteria);

      if (!$id) {

        $this->importSingle($ordine, $shop_id);

      }
      else {

        $today = date('Y-m-d');
        $newdate = date('Y-m-d', strtotime ( '-10 day' , strtotime ( $today ) ) );

        if ($ordine["created_at"] < $newdate)
          echo "Ordine con più di 10 giorni non chiuso sul negozio online [$shop] [{$ordine["order_id"]}]\n";

        $criteria = [
          ['name', '=', $ordine["order_id"]],
        ];

        $fields = ["state"];

        $order = $this->client->search_read('sale.order', $criteria, $fields, 1);

        if ($order[0]["state"] == "manual") {
          if ($type=="magento2")
            $source = "https://www.$shop/stampape/galileo/complete/reference/{$ordine["order_id"]}";
          else
            $source = "https://www.$shop/feed/galileo/complete.php?reference={$ordine["order_id"]}";
        }

        if ($order[0]["state"] == "cancel") {
          if ($type=="magento2")
            $source = "https://www.$shop/stampape/galileo/cancel/reference/{$ordine["order_id"]}";
          else
            $source = "https://www.$shop/feed/galileo/cancel.php?reference={$ordine["order_id"]}";
        }

        //echo $source."\n";

        if ($source) {

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
          curl_setopt($ch, CURLOPT_URL, $source);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $data = curl_exec ($ch);
          curl_close ($ch);

        }

      }

    }

  }

  /**
   Elaborazione degli ordini di un singolo negozio
   **/
  private function importSingle($ordine, $shop_id) {

    // Ordine non esistente, procedo col suo inserimento

    // Process del partner
    $id_customer = $this->processCustomer($ordine["bp"], $ordine["address_invoice"]);

    // Verifica indirizzo di spedizione se diverso da quello di fatturazione
    $diff = array_diff($ordine["address_invoice"], $ordine["address_shipping"]);

    if (sizeof($diff)>0)
      $id_partner_shipping_id = $this->processAddress($ordine["address_shipping"], $id_customer);
    else
      $id_partner_shipping_id = $id_customer;

    //print $ordine["payment_method"]."\n";

    // Preparazione array di inserimento
    $data = [
      "transaction_id" => isset($ordine["last_trans_id"]) ? $ordine["last_trans_id"] : null,
      "name" => $ordine["order_id"],
      "date_order" => $ordine["created_at"],
      "partner_id" => $id_customer,
      "partner_shipping_id" => $id_partner_shipping_id,
      "payment_method_id" => $this->payment_method[$ordine["payment_method"]],
      "payment_term" => $this->payment_term[$ordine["payment_method"]],
      "shop_id" => $shop_id,
      "note" => isset($ordine["message"]) ? $ordine["message"] : null,
    ];

    // Creazione ordine
    if ($this->insert)
      $id = $this->client->create('sale.order', $data);
    else {
        print_r($data);
        $id = null;
    }

    if (is_array($id)) {
      Util::printError($this->shop_url." [creazione ordine]", $id);
      return;
    }

    // Process delle rige d'ordine
    $tipologia = array();
    foreach ($ordine["product"] as $key => $value) {

      if (!in_array($value["tipologia"], $tipologia))
        $tipologia[] = $value["tipologia"];

      // Process del prodotto
      $id_product = $this->processProduct($value);

      // Preparazione array di inserimento
      $data = [
        "product_id" => $id_product,
        "product_uom_qty" => $value["qty_ordered"],
        "price_unit" => $value["price"],
        "order_id" => $id,
        "name" => $value["name"],
      ];

      // Creazione riga d'ordine
      if ($this->insert)
        $id_line = $this->client->create('sale.order.line', $data);
      else {
        print_r($data);
        $id_line = null;
      }

      if (is_array($id_line))
        Util::printError($this->shop_url." [creazione linea]", $id_line);

    }

    // Imposto la tipologia dell'ordine
    if (sizeof($tipologia)>1)
      $client_order_ref = "misto";
    else
      $client_order_ref = $tipologia[0];

    $data = [
      "origin" => $client_order_ref
    ];

    if ($this->insert)
      $this->client->write('sale.order', $id, $data);

    $purchase_price = 0;
    if ($tipologia[0]=="originale" || $tipologia[0]=="misto")
      $purchase_price = 6.9;
    else
      $purchase_price = 2.87;

    // Spese di spedizione
    if ($ordine["payment_method"]=="cashondelivery")
      $purchase_price += 3.5;

    $data = [
      "product_id" => $this->product_shipping,
      "product_uom_qty" => 1,
      "price_unit" => $ordine["shipping_amount"],
      "order_id" => $id,
      "purchase_price" => $purchase_price
    ];

    if ($this->insert)
      $id_line = $this->client->create('sale.order.line', $data);
    else {
      print_r($data);
      $id_line = null;
    }

    if (is_array($id_line))
      Util::printError($this->shop_url." [creazione linea]", $id_line);

    //}

    // Spese di incasso
    if ($ordine["cod_fee"] > 0) {

      $data = [
        "product_id" => $this->product_cashondelivery,
        "product_uom_qty" => 1,
        "price_unit" => $ordine["cod_fee"],
        "order_id" => $id,
        "purchase_price" => 3.5
      ];

      if ($this->insert)
        $id_line = $this->client->create('sale.order.line', $data);
      else {
        print_r($data);
        $id_line = null;
      }

      if (is_array($id_line))
        Util::printError($this->shop_url." [creazione linea]", $id_line);

    }

    // Sconto
    if ($ordine["discount_amount"] <> 0) {

      if ($ordine["discount_amount"] > 0)
        $ordine["discount_amount"] = $ordine["discount_amount"] * -1;

      $data = [
        "product_id" => $this->product_discount,
        "product_uom_qty" => 1,
        "price_unit" => $ordine["discount_amount"],
        "order_id" => $id,
      ];

      if ($this->insert)
        $id_line = $this->client->create('sale.order.line', $data, $ordine["order_id"].".".$this->product_cashondelivery);
      else {
        print_r($data);
        $id_line = null;
      }

      if (is_array($id_line))
        Util::printError($this->shop_url." [creazione linea]", $id_line);

    }

    if (!$this->insert)
      return;

    $order = $this->client->search_read('sale.order', [["id", "=", $id]], ["amount_total"], 1);

    if ($order[0]["amount_total"] != $ordine["grand_total"])
      Util::printError($this->shop_url, "[{$ordine["reference"]}] Ordine con totali errati\n", $ordine["order_id"]);

  }

  /**
   Elaborazione del partner
   **/
  private function processCustomer($customer, $address) {

    //print_r($customer);

    // se l'azienda è privato si import il cliente come privato
    if ($customer["company"] == "PRIVATO" || $customer["company"] == "CASA") {
      $customer["company"] = "";
      $customer["is_business_address"] = 0;
    }

    // Se l'azienda è ditta individuale si imposta il cliente come nome cognome
    if ($customer["company"] == "DITTA INDIVIDUALE") {
      $customer["company"] = $customer["firstname"] . " " . $customer["lastname"];
    }

    // Se l'azienda è generaica si imposta il cliente come azienda nome cognome
    if (in_array($customer["company"], $this->company))
      $customer["company"] = $customer["company"]." ".$customer["firstname"]." ".$customer["lastname"];

    // Se è un azienda si verificano codice fiscale e partita iva
    if ($customer["is_business_address"] == 1) {

      if (!$customer["vatnumber"] && $customer["taxcode"] && strlen($customer["taxcode"])==11)
        $customer["vatnumber"] = $customer["taxcode"];

      if (!$customer["taxcode"] && $customer["vatnumber"])
        $customer["taxcode"] = $customer["vatnumber"];

      if ($customer["company"] == "")
        $customer["company"] = $customer["firstname"] . " " . $customer["lastname"];

    }

    if ($customer["vatnumber"] && substr($customer["vatnumber"], 0, 2) != "IT")
      $customer["vatnumber"] = "IT".$customer["vatnumber"];

    if (!$customer["vatnumber"] && !$customer["taxcode"] && $customer["is_business_address"] == 1)
      $customer["is_business_address"] = 0;

    if ($customer["company"] && $customer["vatnumber"] && !$customer["is_business_address"])
      $customer["is_business_address"] = 1;

    if ($customer["is_business_address"]==null)
      $customer["is_business_address"] = 0;

    // Criteri di ricerca del partner
    if ($customer["vatnumber"] && $customer["taxcode"]) {
      // Criteri di ricerca per piva/cf o per email
      $criteria = [
        ['vat', '=', $customer["vatnumber"]],
        ['fiscalcode', '=', $customer["taxcode"]],
      ];

    }
    else {
      // Criteri di ricerca per email
      $criteria = [
        ['email', '=', $customer["email"]],
      ];
    }

    //print_r($criteria);

    // Recupero il partner
    $id = $this->client->searchOne('res.partner', $criteria);

    // Ricerca del codice provincia
    $criteria = [
      ['code', '=', $address["region_code"]],
    ];

    $state_id = $this->client->searchOne('res.country.state', $criteria);

    // Preparazione array di inserimento
    $data = [
      "name" => ($customer["company"]!="") ? $customer["company"] : $customer["firstname"]." ".$customer["lastname"],
      "firstname" => $customer["firstname"],
      "lastname" => $customer["lastname"],
      "is_company" => true,
      "individual" => !$customer["is_business_address"],
      "street" => $address["street"],
      "city" => $address["city"],
      "zip" => $address["postcode"],
      "state_id" => $state_id,
      "email" => $customer["email"],
      "phone" => $customer["telephone"],
      "vat" => $customer["vatnumber"],
      "fiscalcode" => $customer["taxcode"],
    ];

    if (!$id) {

      $data["notify_email"] = "always";
      $data["property_account_position"] = 1;
      $data["property_account_receivable"] = 185;
      $data["property_account_payable"] = 348;
      $data["country_id"] = 110;

      //print_r($data);
      //print_r($customer);

      // Creazione nuovo partner
      if ($this->insert)
        $id = $this->client->create('res.partner', $data);
      else {
        print "res.partner insert\n";
        print_r($data);
      }

      if (is_array($id))
        Util::printError($this->shop_url." [creazione partner]", $id);

    }
    else {

      // Aggiornamento dei dati del partner esistente

      // Recupero dati da odoo
      $fields = ['name', 'street', 'city', 'zip', 'phone', 'email', 'vat', 'fiscalcode', 'state_id', 'ref', 'firstname', 'lastname'];

      $customer = $this->client->read('res.partner', $id, $fields);
      unset($customer["id"]);
      $customer["state_id"] = $customer["state_id"][0];

      // Array delle differenza fra i dati recuperati odoo e quelli preparati per essere modificati
      $diff = array_diff($customer, $data);

      // Se la dimensione dell'array è maggiore di 0 significa che sono state
      // trovate delle differenze e si procede con l'aggiornamento del partner
      if ($this->insert) {
        if (sizeof($diff)>0)
          $this->client->write('res.partner', $id, $data);
      }
      else {
        print "res.partner update\n";
        print_r($data);
      }

    }

    return $id;

  }

  private function processAddress($address, $parent_id) {

    // se l'azienda è privato si import il cliente come privato
    if ($address["company"] == "PRIVATO" || $address["company"] == "CASA") {
      $address["company"] = "";
      //$address["is_business_address"] = 0;
    }

    // Se l'azienda è ditta individuale si imposta il cliente come nome cognome
    if ($address["company"] == "DITTA INDIVIDUALE") {
      $address["company"] = $address["firstname"] . " " . $address["lastname"];
    }

    // Se l'azienda è generaica si imposta il cliente come azienda nome cognome
    if (in_array($address["company"], $this->company))
      $address["company"] = $address["company"]." ".$address["firstname"]." ".$address["lastname"];

    // Criteri di ricerca del partner indirizzo
    $criteria = [
      ['parent_id', '=', $parent_id],
    ];

    // Recupero il partner
    $id = $this->client->searchOne('res.partner', $criteria);

    // Ricerca del codice provincia
    $criteria = [
      ['code', '=', $address["region_code"]],
    ];

    $state_id = $this->client->searchOne('res.country.state', $criteria);

    // Preparazione array di inserimento
    $data = [
      "name" => ($address["company"]!="") ? $address["company"] : $address["firstname"]." ".$address["lastname"],
      "firstname" => $address["firstname"],
      "lastname" => $address["lastname"],
      "street" => $address["street"],
      "city" => $address["city"],
      "zip" => $address["postcode"],
      "state_id" => $state_id,
      "ref" => $address["firstname"]." ".$address["lastname"],
      "parent_id" => $parent_id,
      "type" => "delivery"
    ];

    /*echo "Indirizzo creato\n";
    print_r($data);*/

    if (!$id) {

      $data["country_id"] = 110;
      $data["is_company"] = 0;

      // Creazione nuovo partner
      if ($this->insert)
        $id = $this->client->create('res.partner', $data);

      if (is_array($id))
        Util::printError($this->shop_url." [creazione partner]", $id);

    }
    else {

      // Aggiornamento dei dati del partner esistente

      // Recupero dati da odoo
      $fields = ['name', 'street', 'city', 'zip', 'ref', "state_id", 'firstname', 'lastname'];

      $customer = $this->client->read('res.partner', $id, $fields);
      unset($customer["id"]);
      $customer["state_id"] = $customer["state_id"][0];

      // Array delle differenza fra i dati recuperati odoo e quelli preparati per essere modificati
      $diff = array_diff($customer, $data);

      // Se la dimensione dell'array è maggiore di 0 significa che sono state
      // trovate delle differenze e si procede con l'aggiornamento del partner
      if (sizeof($diff)>0 && $this->insert)
        $this->client->write('res.partner', $id, $data);

    }

    return $id;

  }

  /**
    Elaborazione del Prodotto
    **/
  private function processProduct($product) {

    // Verifico se il codice del prodotto è di tipo TIPOLOGIA/OEM.SERIE (Stampaperfetta)
    $n = strpos($product["oem"], ".");
    $sku = $product["oem"];

    if ($n !== false) {
      // Se la verifica ha dato esito positivo si taglia il .SERIE
      $sku = substr($sku, 0, $n);
    }

    // Verifica della tipologia di prodotto
    if ($product["tipologia"]=="compatibile") {
      $name = "Toner compatibile con $sku";
      $sku = "TC/$sku";
      $categ_id = $this->category_compatibile;
    }
    else {
      $name = "Toner originale $sku";
      $sku = "TO/$sku";
      $categ_id = $this->category_originale;
    }

    // Ricerca del prodotto in odoo
    $criteria = [
      ['default_code', '=', $sku],
    ];

    $id = $this->client->searchOne('product.product', $criteria);

    // Se il prodotto esiste già non si fa niente
    if ($id)
      return $id;

    // Preparazione array di inserimento
    $data = [
      'name' => $name,
      'sale_ok' => true,
      'type' => 'consu',
      'active' => true,
      'default_code' => $sku,
      'categ_id' => $categ_id,
      "list_price" => 0,
      "taxes_id" => [[6, 0, [120]]]
    ];

    // Creazione del prodotto
    if ($this->insert)
      $id = $this->client->create('product.product', $data);

    if (is_array($id))
      Util::printError($this->shop_url." [creazione product]", $id);

    return $id;

  }

  public function invoiceFromOrder() {

    $ids = $this->client->search("sale.order", [
      ["state", "=", "manual"],
      ["shop_id", "!=", False]
    ]);

    if (sizeof($ids)==0)
      return;

    $result = $this->client->execute("sale.order", "action_invoice_create", [$ids]);

    if (isset($result["faultCode"])) {
      Util::printError("ImportOrder.invoiceFromOrder", $result["faultString"]);
    }

  }

  /*public function setPurchasePrice() {

    $sql = "select l.id, p.default_code, h.cost
  from sale_order o
    join sale_order_line l on o.id = l.order_id
    join product_product p on l.product_id = p.id
    join product_price_history h on p.product_tmpl_id = h.product_template_id and h.cost > 0
 where o.date_order >= '01/01/2018'
   and l.product_id not in (6, 7, 8)
   and l.purchase_price = 0
   and h.create_date = (select max(h2.create_date) from product_price_history h2 where h2.product_template_id = h.product_template_id and h2.cost > 0 and h2.create_date < o.date_order)";

     $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

     foreach ($result as $value) {

       $data = [
         "purchase_price" => $value["cost"]
       ];

       $this->client->write('sale.order.line', $value["id"], $data);

     }

     $sql = "select l.id, p.default_code, h.cost
   from sale_order o
     join sale_order_line l on o.id = l.order_id
     join product_product p on l.product_id = p.id
     join product_price_history h on p.product_tmpl_id = h.product_template_id and h.cost > 0
  where o.date_order >= '01/01/2018'
    and l.product_id not in (6, 7, 8)
    and l.purchase_price = 0
    and h.create_date = (select max(h2.create_date) from product_price_history h2 where h2.product_template_id = h.product_template_id and h2.cost > 0)";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    foreach ($result as $value) {

      $data = [
        "purchase_price" => $value["cost"]
      ];

      $this->client->write('sale.order.line', $value["id"], $data);

    }

  }*/

  public function checkPartnerVat() {

    $sql = "select i.number, i.date_invoice, p.name as partner_name, s.name as shop_name, o.name as order_number, p.email
  from account_invoice i
    join res_partner p on i.partner_id = p.id
    join sale_order_invoice_rel r on r.invoice_id = i.id
    join sale_order o on o.id = r.order_id
    join sale_shop s on s.id = o.shop_id
 where i.journal_id = 15
   and p.vat = ''
   and p.fiscalcode = ''
 order by 1";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    foreach ($result as $value) {

      \Yii::$app->mailer->compose()
        ->setFrom('cesare@pointec.it')
        ->setTo($value["email"])
        ->setSubject('Richiesta dati per fattura')
        ->setTextBody("Buongiorno, in data {$value["date_invoice"]} abbiamo emesso fattura relativa all'ordine n° {$value["order_number"]} sul nostro sito {$value["shop_name"]}.\n\nDa un controllo risulta che i suoi dati non sono completi. Manca il codice fiscale e la partita iva.\n\nPotrebbe gentilmente comunicarceli all'indirizzo cesare@pointec.it\n\nGrazie per la collaborazione, Cesare, Pointec srl")
        ->send();

    }

  }

}
