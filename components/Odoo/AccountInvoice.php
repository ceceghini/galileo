<?php

namespace app\components\Odoo;

use app\components\OdooClient as Client;
use app\components\Util;

class AccountInvoice {

  private $noinsert = false;

  private $_client;
  private $_folder = "/opt/files/fatture";
  private $_folder_test = "/opt/files/fatture_test";
  private $_test = false;
  private $_in_parse;
  private $_parsedText;

  private $partner_bank = [
    1 => 4,      # Contrassegno
    2 => 1,      # Bonifico bancario
    3 => 2,      # Paypal
    4 => 3       # Payplug
  ];

  private $account_id = [
    "italia" => 348,
    "intra" => 349
  ];
  private $journal_id = [
    "italia" => 3,
    "intra" => 22
  ];
  private $fiscal_position = [
    "italia" => 1,
    "intra" => 2
  ];
  private $tax_id = [
    "italia" => 20,
    "intra" => 122
  ];
  private $parent_id = [
    "italia" => 10,
    "intra" => 13
  ];

  private $registration_date;

  function __construct() {

    $this->_client = new Client();

  }

  /**
  Invio fatture ai clienti
  **/
  public function sendInvoice() {

    $account_invoice_ids = $this->_client->search("account.invoice",
      [
        ["state", "=", "open"],
        ["type", "=", "out_invoice"],
        ["journal_id", "=", 15],
        ["sent", "=", False]
      ]);

    foreach ($account_invoice_ids as $account_invoice_id) {

      $result = $this->_client->execute("email.template", "send_mail",
      [
        4,  // Template invio
        $account_invoice_id,
        True
      ]);

      if (isset($result["faultCode"])) {
        Util::printError("AccountInvoice.sendInvoice", $result["faultString"]);
      }

      $data = [
        "sent" => true
      ];

      $result = $this->_client->write("account.invoice", $account_invoice_id, $data);

      if (isset($result["faultCode"])) {
        Util::printError("AccountInvoice.sendInvoice", $result["faultString"]);
      }

    }

  }

  /**
  Validazione fattura fornitori
  **/
  public function validateIn() {

    //return;

    // Recupero l'ultima data di registrazione delle fatture
    $sql = "select journal_id, max(registration_date) as registration_date
              from account_invoice
             where state in ('open', 'paid')
               and type = 'in_invoice'
             group by journal_id";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    foreach ($result as $value) {
      $this->registration_date[$value["journal_id"]] = $value["registration_date"];
    }

    // Recupero fatture da validare
    $account_invoices = $this->_client->search_read("account.invoice",
      [
        ["state", "=", "draft"],
        ["type", "=", "in_invoice"],
        //["user_id", "=", 6] // Create da galileo... in automatico
      ],
      [
        "journal_id",
        "date_invoice",
        "period_id",
      ]);

    foreach ($account_invoices as $account_invoice) {

      // Se il sezionale non è valorizzato non si procede con la validazione
      if (!isset($this->registration_date[$account_invoice["journal_id"][0]]))
        continue;

      $registration_date = $this->registration_date[$account_invoice["journal_id"][0]];
      $registration_year = substr($registration_date, 0, 4);

      $invoice_year = substr($account_invoice["date_invoice"], 0, 4);

      // Si tratta di una fattura che fa riferimento all'anno prima del anno corrente
      // Va validata a mano
      //print "$invoice_year < $registration_year\n";
      if ($invoice_year < $registration_year)
        continue;

      if ($account_invoice["date_invoice"] <= $registration_date) {
        // Fattura con data inferiore alla data di ultima registrazione
        // Si procede col registrare la fattura alla data ultima di registrazione
        $this->validateSingleIn($account_invoice, $registration_date);
      }
      else {

        $last_day = date("Y-m-t", strtotime($account_invoice["date_invoice"]));
        $today = date("Y-m-d");

        if ($today > $last_day) {

          $this->validateSingleIn($account_invoice, $last_day);

          $this->registration_date[$account_invoice["journal_id"][0]] = $last_day;

        }

      }

    }

    /*$result = $this->_client->execute("pointec.pointec", "validate_invoice",
      [
        $account_invoice_ids,
      ]);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.validateIn", $result["faultString"]);
    }*/

  }

  private function validateSingleIn($account_invoice, $registration_date) {

    // Imposto la data di registrazione e valido la fattura
    $data = [
      "registration_date" => $registration_date
    ];

    $result = $this->_client->write("account.invoice", $account_invoice["id"], $data);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.validateSingleIn", $result["faultString"]);
    }

    // Validazione della fattura
    $result = $this->_client->execute("pointec.pointec", "validate_invoice",
      [
        [$account_invoice["id"]],
      ]);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.validateSingleIn [{$account_invoice["id"]}]", $result["faultString"]);
    }

    // Recupero allegato
    $attachment_id = $this->_client->search("ir.attachment",
      [
        ["res_id", "=", $account_invoice["id"]],
        ["res_model", "=", "account.invoice"],
      ]
    );

    $name = $account_invoice["id"]["period_id"][1]."--".str_replace("/", "-", $account_invoice["id"]["number"]).".pdf";

    $data = [
      "name" => $name
    ];

    $out = $this->_client->write("ir.attachment", $attachment_id[0], $data);

  }

  public function prepareIntra() {

    $folder = "/opt/files/iva/intra";

    exec("rm -r -f ".$folder."/*");

    $period = $this->_client->search("account.period",
    [
      ["special", "=", False],
      ["state", "=", "draft"]
    ], 0, 1);

    $account_invoice_ids = $this->_client->search("account.invoice",
      [
        ["period_id", "=", $period[0]],
        ["journal_id", "=", 22]
      ]);

    foreach ($account_invoice_ids as $account_invoice_id) {

      $attachment = $this->_client->search_read("ir.attachment",
        [
          ["res_id", "=", $account_invoice_id],
          ["res_model", "=", "account.invoice"],
        ],
        ["name", "datas"]
      );

      $pdf_decoded = base64_decode ($attachment[0]["datas"]);
      $pdf = fopen ("$folder/{$attachment[0]["name"]}",'w');
      fwrite ($pdf,$pdf_decoded);
      fclose ($pdf);

      echo "File salvato [$folder/{$attachment[0]["name"]}]\nexit";

    }

  }

  /**
  Validazione fatture clienti
  **/
  public function validateOut() {

    $account_invoice_ids = $this->_client->search("account.invoice",
      [
        ["state", "=", "draft"],
        ["type", "=", "out_invoice"],
        ["origin", "!=", False]
      ]);

    foreach ($account_invoice_ids as $account_invoice_id) {

      $account_invoice = $this->_client->read('account.invoice',
        $account_invoice_id,
        [
          "sale_ids",
          "partner_id"
        ]);

      // Se la fattura non ha nessun ordine di vendita non si fa niente
      if (!isset($account_invoice["sale_ids"][0]))
        continue;

      // Recupero l'ordine di vendita
      $sale_order = $this->_client->read("sale.order",
        $account_invoice["sale_ids"][0],
        [
          "payment_method_id"
        ]);

      // Recupero del c/c bancario
      $partner_bank_id = $this->partner_bank[$sale_order["payment_method_id"][0]];

      // Recupero del partner
      $partner = $this->_client->read("res.partner",
        $account_invoice["partner_id"][0],
        [
          "individual"
        ]);

      // Imposto il sezionale sulla base della tipologia del partner
      if ($partner["individual"])
        $journal_id = 16;
      else
        $journal_id = 15;

      // Dati da scrivere
      $data = [
        "partner_bank_id" => $partner_bank_id,
        "journal_id" => $journal_id
      ];

      $result = $this->_client->write("account.invoice", $account_invoice_id, $data);

      if (isset($result["faultCode"])) {
        Util::printError("AccountInvoice.validateOut", $result["faultString"]);
      }

    }

    $result = $this->_client->execute("pointec.pointec", "validate_invoice",
      [
        $account_invoice_ids,
      ]);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.validateOut", $result["faultString"]);
    }

  }

  public function getInvoiceManual() {

    $ret = array();

    foreach (new \DirectoryIterator($this->_folder) as $d) {

      // Esclusione directory con il .
      if($d->isDot()) continue;

      // Esclusione directori DS_Store
      if (strpos($d->getFileName(),"DS_Store")!==false) continue;

      foreach (new \DirectoryIterator($d->getRealPath()) as $f) {

        if($f->isDot() || $f->getFileName()==".DAV") continue;

        $file = [
          "name" => $f->getRealPath()
        ];

        $ret[] = $file;

      }

    }

    return $ret;

  }

  /**
  Import fatture forntiori
  **/
  public function importIn($test = false) {

    //Util::setDebug();

    $this->_in_parse = require __DIR__ . '/Data/parse_in.php';

    if ($test) {
        $folder = $this->_folder_test;
        $this->_test = true;
    }
    else
      $folder = $this->_folder;

    Util::debug($folder);

    foreach (new \DirectoryIterator($folder) as $d) {

      // Esclusione directory con il .
      if($d->isDot()) continue;

      // Esclusione directori DS_Store
      if (strpos($d->getFileName(),"DS_Store")!==false) continue;

      // Elaborazione singolo fornitore
      $this->elaboraFornitore($d);

    }

  }

  // Ridimensionamento pdf e conversioni a pdf/a
  public function reducePdf() {

      // Ridimensionamento fatture acquisti
      foreach (new \DirectoryIterator($this->_folder) as $d) {
        if($d->isDot()) continue; // Esclusione folder . e ..
        if (strpos($d->getFileName(),"DS_Store")!==false) continue; // Esclusione folder DS_STORE

        chmod($d->getRealPath(), 0777); // Imposto permessi

        foreach (new \DirectoryIterator($d->getRealPath()) as $f) {

          if($f->isDot() || $f->getFileName()==".DAV") continue;

          //Util::debug($f->getFileName());

          if (strpos($f->getFileName(),"__")===false)
            Util::reducePdf($f);

        }
      }

      // Ridimensionamento note accredito
      foreach (new \DirectoryIterator("/opt/files/notecr") as $f) {

        if($f->isDot() || $f->getFileName()==".DAV") continue; // Esclusione folder . e ..
        if (strpos($d->getFileName(),"DS_Store")!==false) continue; // Esclusione folder DS_STORE

        if (strpos($f->getFileName(),"__")===false)
          Util::reducePdf($f);

      }

  }

  // Elaborazione cartella
  private function elaboraFornitore($d) {

    Util::debug("Elaborazione [{$d->getFileName()}]");

    chmod($d->getRealPath(), 0777);

    if (isset($this->_in_parse[$d->getFileName()]))
      $pf = $this->_in_parse[$d->getFileName()];
    else
      $pf = null;

    foreach (new \DirectoryIterator($d->getRealPath()) as $f) {

      if($f->isDot() || $f->getFileName()==".DAV") continue;

      if ($pf) {
        if (strpos($f->getFileName(),"__")!==false)
          $i = $this->extractFromFile($f, $pf, $d);
      }

    }

  }

  // Elaborazione singolo file
  private function extractFromFile($f, $pf, $d) {

    echo $f->getRealPath()."\n";

    $i = array();
    $i["filename"] = $f->getFileName();
    $i["fullpath"] = $f->getRealPath();

    $this->_parsedText = \Spatie\PdfToText\Pdf::getText($f->getRealPath());
    $this->_parsedText = str_replace("\n\n", "\n", $this->_parsedText);

    // Verifico se ci sono delle condizioni di esclusione
    if (isset($pf["exclude"])) {
      if ($pf["exclude"] != "") {
        $pos = strpos($this->_parsedText, $pf["exclude"]);
  			if ($pos !== false) {
  				echo("[{$f->getRealPath()}] scartato");
  				return null;
        }
      }
    }

    // Numero documento
    $i["supplier_invoice_number"] = $this->parseValue($pf["supplier_invoice_number"]);
    if (!$i["supplier_invoice_number"]) {
      Util::printError("Numero fattura non decodificata [".$f->getRealPath()."]", "fatture_in");
    }
    // Data fattura
    $i["date_invoice"] = \DateTime::createFromFormat($pf["date_format"], $this->parseValue($pf["date_invoice"]))->format('Y-m-d');
    // Totale documento
    $i["amount_total"] = Util::getImporto($this->parseValue($pf["amount_total"]));

    if ($this->_test) {
      print $this->_parsedText."\n\n";
      print_r($i);
      print_r ($pf["amount_total"]);
      return;
    }

    $this->insertInvoice($i, $pf);

  }

  // Inserimento fattura fornitore
  private function insertInvoice($i, $pf) {

    if ($this->noinsert) {
      print_r($i);
      return;
    }

    if ($i["amount_total"]==0) {
      echo "Fattura decodificata a 0 [{$i["fullpath"]}]\n";
      //print_r($pf["amount_total"]);
      return;
    }

    // Verifica se la fattura è già presente o meno
    $criteria = [
      ['supplier_invoice_number', '=', $i["supplier_invoice_number"]],
      ['partner_id', '=', $pf["res_partner_id"]],
      ['date_invoice', '=', $i["date_invoice"]],
    ];

    $id = $this->_client->search("account.invoice", $criteria);

    if (is_array($id))
      if (sizeof($id)>0) {
        echo "Fattura gia presente in odoo [{$i["fullpath"]}]\n";
        return;
      }

    // Verifico se si tratta di un intra o meno
    $key = "italia";
    if (isset($pf["intra"])) {
      if ($pf["intra"])
        $key = "intra";
    }

    if ($key == "intra")
      $amount_total = $i["amount_total"] * 1.22;
    else
      $amount_total = $i["amount_total"];

    // Praparazione dati fattura
    $data = [
      "partner_id" => $pf["res_partner_id"],
      "supplier_invoice_number" => $i["supplier_invoice_number"],
      "date_invoice" => $i["date_invoice"],
      "account_id" => $this->account_id[$key],
      "journal_id" => $this->journal_id[$key],
      "fiscal_position" => $this->fiscal_position[$key],
      "type" => "in_invoice",
      "check_total" => $amount_total,
      //"reference" => $nomefile
    ];

    // Inserimento della fattura
    $id = 0;
    if (isset($pf["insert"])) {
      if ($pf["insert"]==true) {
        $id = $this->_client->create('account.invoice', $data);

        if (is_array($id)) {
          Util::printError("Errore inserimento fattura", $id);
          return;
        }
      }
    }
    if ($id==0) {
      print_r($data);
    }

    if ($key == "intra") {
      $amount_total = $i["amount_total"];
      $rc = true;
    }
    else {
      $amount_total = $i["amount_total"] / 1.22;
      $rc = false;
    }

    // Preparazione riga fattura
    $data = [
      "account_id" => $pf["account_id"],
      "name" => $pf["name"],
      "product_uom_qty" => 1,
      "price_unit" => $amount_total,
      "invoice_id" => $id,
      "invoice_line_tax_id" => [[6, 0, [$this->tax_id[$key]]]],
      "rc" => $rc
    ];

    // Inserimento riga fattura
    $id_line = 0;
    if (isset($pf["insert"])) {
      if ($pf["insert"]==true) {
        $id_line = $this->_client->create('account.invoice.line', $data);

        if (is_array($id_line))
          Util::printError("Errore inserimento riga fattura", $id_line);

        echo "Fattura inserita [{$i["filename"]}]\n";
      }
    }
    if ($id_line==0) {
      print_r($data);
      return;
    }

    // Upload del pdf
    $pdf = file_get_contents($i["fullpath"]);
    $base64 = chunk_split(base64_encode($pdf));

    $data = [
      'name' => str_replace("__", "", $i["filename"]),
      'type' => 'binary',
      'datas' => $base64,
      'datas_fname' => str_replace("__", "", $i["filename"]),
      'res_model' => 'account.invoice',
      'res_id' => $id,
      'mimetype' => 'application/x-pdf',
      'parent_id' => $this->parent_id[$key] // Cartella fatture fornitori
    ];

    $result = $this->_client->create('ir.attachment', $data);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.insertInvoice", $result["faultString"]);
    }

    /*$result = $this->_client->execute("pointec.pointec", "validate_invoice",
      [
        [$id],
      ]);

    if (isset($result["faultCode"])) {
      Util::printError("AccountInvoice.insertInvoice", $result["faultString"]);
    }*/

    unlink($i["fullpath"]);

  }

  // Effettua il parsing di una stringa tramite regex
  private function parseValue($regex) {

    /*print "- a --------------------------------\n";
    print_r($regex);
    print "- b --------------------------------\n";*/

    foreach ($regex as $value) {

      /*echo $value."\n";
      print "- c --------------------------------\n";*/

      if (preg_match("/$value/i", $this->_parsedText, $matcher)) {

        //print_r($matcher);
        if ($matcher[1]!="")
          return $matcher[1];
      }

    }

  }

  // Riapertura massiva delle fatture
  public function reopenInvoice() {

    $client = new \app\components\OdooClient();
    $account_invoice_ids = $client->search("account.invoice",
      [
        ["state", "=", "open"],
        ["type", "=", "in_invoice"],
        ["journal_id", "=", 22]
      ]);

      $result = $client->execute("account.invoice", "signal_workflow",
        [
          $account_invoice_ids,
          "invoice_cancel"
        ]);

      print_r($result);

  }

}
