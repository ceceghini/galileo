<?php

namespace app\components\Odoo\AccountBank;

use app\components\Odoo\AccountBank;
use app\components\Util;

class Paypal extends AccountBank {

  protected $_subpath = "paypal";

  protected $charge_partner_id = null;
  protected $charge_name = "Commissioni paypal";
  protected $journal_id = 11;
  protected $prefix_name = "PAYPAL";
  protected $pdf_name = "paypal";

  public function elaboraFile($filename) {

    // Recupero il contenuto del file
    $file = file_get_contents($filename);
    $file = str_replace('"Pagamenti su sito web"', "''", $file);
    // Array delle righe del file
    $lines = explode("\n", $file);

    unset ($lines[0]);

    // Calcolo balance start
    $value = $lines[1];
    $data = str_getcsv($value, ',', '"');
    $this->balance_start = (float)Util::getImporto($data[8]);
    $netto = (float)Util::getImporto($data[7]);
    $this->balance_start -= $netto;

    // Calcolo banlance end
    $value = $lines[sizeof($lines)-1];
    $data = str_getcsv($value, ',', '"');
    $this->balance_end_real = (float)Util::getImporto($data[8]);

    // Loop fra le righe del file
    foreach ($lines as $value) {
      $data = str_getcsv($value, ',', '"');

      if (sizeof($data)<=1)
        continue;

      if ($data[3]=="Blocco conto per autorizzazione aperta") {
        continue;
      }

      if ($data[3]=="Storno di blocco conto generico") {
        continue;
      }

      if ($data[3]=="Pagamento diretto con carta di credito") {
        $this->insertPayment($data);
        continue;
      }

      if ($data[3]=="Importo totale incasso cumulativo") {
        $this->updatePayment($data[17]);
        continue;
      }

      if ($data[3]=="Trasferimento generico (conto bancario)") {
        $this->balance_end_real -= (float)Util::getImporto($data[5]);
        continue;
      }

      if ($data[3]=="Tariffa pagamento") {
        $charge = (float)Util::getImporto($data[5]);
        $this->charge_amt += $charge;
        continue;
      }

      $line = array();

      $date = \DateTime::createFromFormat('d/m/Y', $data[0]);
      $line["DATE"] = $date->format('Y-m-d');

      if ($data[3]=="Importo incasso cumulativo completato") {
        $payment = $this->getPayment($data[17]);
        $line["TRXID"] = $payment["transaction_id"];
        $line["DESCRIPTION"] = $data[3] . " - " . $payment["email"]. " - ".$payment["name"]." - ".$payment["numero_ordine"];
        $charge = 0;
      }
      else {
        $line["TRXID"] = $data[9];
        $line["DESCRIPTION"] = $data[3] . " - " . $data[10]. " - ".$data[11]. " - ".$data[16];
        $charge = (float)Util::getImporto($data[6]);
      }
      $line["AMT"] = (float) Util::getImporto($data[5]);

      $this->charge_amt += $charge;

      $this->lines[] = $line;

    }

  }

  private function getPayment($transaction_id) {

    $result = \Yii::$app->db->createCommand("select * from paypal_payment where state = 1 and transaction_id = :transaction_id")
      ->bindValue(":transaction_id", $transaction_id)
      ->queryOne();

    \Yii::$app->db->createCommand()->delete("paypal_payment", "transaction_id = :transaction_id", [
      ":transaction_id" => $transaction_id
    ]);

    return $result;

  }

  private function insertPayment($data) {

    $result = \Yii::$app->db->createCommand("select * from paypal_payment where state = 1 and transaction_id = :transaction_id")
      ->bindValue(":transaction_id", $data[9])
      ->queryOne();

    if ($result)
      return;

    \Yii::$app->db->createCommand()->insert("paypal_payment", [
      "transaction_id" => $data[9],
      "email" => $data[10],
      "name" => $data[11],
      "importo" => (float)Util::getImporto($data[5]),
      "numero_ordine" => $data[16],
    ])->execute();

  }

  private function updatePayment($transaction_id) {

    $n = \Yii::$app->db->createCommand("select count(*) as n from paypal_payment where transaction_id = :transaction_id")
      ->bindValue(":transaction_id", $transaction_id)
      ->queryScalar();

    if ($n != 1)
      return;

    \Yii::$app->db->createCommand()->update("paypal_payment", [
      "state" => 1,
    ], "transaction_id = :transaction_id", [
       ":transaction_id" => $transaction_id
    ])->execute();

  }

  protected function elaboraPdf($filename) {

    $parsedText = \Spatie\PdfToText\Pdf::getText($filename);

    if(!preg_match("/paypal@pointec.it\n\n(.*) - .*\n\nEstratto conto/i", $parsedText, $matcher))
      return;

    $date = \DateTime::createFromFormat('d/m/Y', $matcher[1]);
    $day = $date->format('Y-m-d');
    $this->first_day_time = strtotime($day);

    $this->uploadPdf($filename);

  }

}

 ?>
