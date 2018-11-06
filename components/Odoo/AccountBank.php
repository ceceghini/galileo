<?php

namespace app\components\Odoo;

use app\components\OdooClient as Client;
use app\components\Util;

class AccountBank {

  private $periodi = [
    "01" => "01",
    "02" => "01",
    "03" => "01",
    "04" => "04",
    "05" => "04",
    "06" => "04",
    "07" => "07",
    "08" => "07",
    "09" => "07",
    "10" => "10",
    "11" => "10",
    "12" => "10",
  ];

  private $_startpath = "/opt/files/estratticonto";

  protected $balance_start;
  protected $balance_end_real;
  protected $name;
  protected $charge_amt;

  protected $lines;

  function __construct() {

    $this->client = new Client();

  }

  // Funzione master per l'import degli estratti conto bancari
  public function import() {

    $path = $this->_startpath . "/" . $this->_subpath;

    foreach (new \DirectoryIterator($path) as $f) {

      if($f->isDot()) continue;
      if ($f->getFilename()==".DAV") continue;

      //echo $f->getFilename()."\n";

      $ext = $f->getExtension();
      $ext = strtolower($ext);

      if ($ext == "csv") {

        $this->lines = array();
        $this->balance_start = 0;
        $this->balance_end_real = 0;
        $this->charge_amt = 0;
        $this->name = null;

        echo "Elaborazione file [{$f->getRealPath()}]\n";

        $this->elaboraFile($f->getRealPath());

        $this->process();

        unlink($f->getRealPath());

      }
      elseif ($ext == "pdf") {

        $this->elaboraPdf($f->getRealPath());

      }

    }

  }

  // Ultimo giorno dell'estratto conto bancario
  protected function getLastDay() {

    return date("Y-m-t", $this->first_day_time);   // Ultimo giorno del mese dell'estratto conto

  }

  // Nome dell'estratto conto bancario
  protected function getName() {

    $month = date("Y-m", $this->first_day_time);        // Mese dell'estratto conto YYYY-MM
    return "{$this->prefix_name} - [$month]";

  }

  protected function getPdfName() {

    $month = date("Y-m", $this->first_day_time);        // Mese dell'estratto conto YYYY-MM
    return "{$this->pdf_name}-$month.pdf";

  }

  // Carica i dati preelaborati in odoo
  private function process() {

    $lines_value = array_values($this->lines);
    $first_line = array_shift($lines_value);

    $first_day = $first_line["DATE"];           // Primo giorno dell'estratto conto
    $this->first_day_time = strtotime($first_day);  // Primo giorno dell'estratto conto in time

    // Recupero i parametri di testata
    $last_day = $this->getLastDay();

    // Recupero del periodo
    $year = date("Y", $this->first_day_time);           // Anno dell'estratto conto YYYY
    $month_number = date("m", $this->first_day_time);   // Mese dell'estratto conto MM
    $period_code = $this->periodi[$month_number]."/".$year;
    $period_id = $this->getPeriodoId($period_code);

    // Recupero del nome
    $name = $this->getName();

    $data = [
      "name" => $name,
      "period_id" => $period_id,
      "date" => $last_day,
      "journal_id" => $this->journal_id,
      "balance_start" => $this->balance_start,
      "balance_end_real" => $this->balance_end_real,
    ];

    $id = $this->client->create('account.bank.statement', $data);

    if (isset($id["faultCode"])) {
      Util::printError("AccountBank.process", $result["faultString"]);
    }

    foreach ($this->lines as $value) {

      $data = [
        "statement_id" => $id,
        "date" => $value["DATE"],
        "name" => $value["DESCRIPTION"],
        "amount" => $value["AMT"],
        "ref" => isset($value["TRXID"]) ? $value["TRXID"] : null,
      ];

      //print_r($data);

      $line_id = $this->client->create('account.bank.statement.line', $data);

      if (isset($line_id["faultCode"])) {
        Util::printError("AccountBank.process", $result["faultString"]);
      }

    }

    if ($this->charge_amt <> 0) {

      $data = [
        "statement_id" => $id,
        "date" => $last_day,
        "name" => $this->charge_name,
        "partner_id" => $this->charge_partner_id,
        "amount" => $this->charge_amt,
      ];

      $line_id = $this->client->create('account.bank.statement.line', $data);

      if (isset($line_id["faultCode"])) {
        Util::printError("AccountBank.process", $result["faultString"]);
      }

    }

    echo "Estratto conto importato [$name]\n";

  }

  // Recupera il periodo dulla base del codice
  private function getPeriodoId($code) {

    $period = $this->client->search("account.period", [
      ["code", "=", $code]
    ]);

    return $period[0];

  }

  protected function uploadPdf($filename) {

    $ids = $this->client->search("account.bank.statement", [
      ["name", "=", $this->getName()]
    ]);

    if (!$ids)
      return;

    $f = new \SplFileInfo($filename);

    $filename = Util::reducePdf($f);

    $pdf = file_get_contents($filename);
    $base64 = chunk_split(base64_encode($pdf));

    $data = [
      'name' => $this->getPdfName(),
      'type' => 'binary',
      'datas' => $base64,
      'datas_fname' => $this->getPdfName(),
      'res_model' => 'account.bank.statement',
      'res_id' => $ids[0],
      'mimetype' => 'application/x-pdf',
      'parent_id' => 11 // Cartella estratti conto bancari
    ];

    $result = $this->client->create('ir.attachment', $data);

    if (isset($result["faultCode"])) {
      Util::printError("AccountBank.uploadPdf", $result["faultString"]);
    }

    unlink($filename);

  }

}
