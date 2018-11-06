<?php

namespace app\components\Odoo\AccountBank;

use app\components\Odoo\AccountBank;
use app\components\Util;

class CrGiovo extends AccountBank {

  protected $_subpath = "crgiovo";

  protected $journal_id = 10;
  protected $prefix_name = "CRGIOVO [37031]";
  protected $pdf_name = "crgiovo";

  public function elaboraFile($filename) {

    // Recupero il contenuto del file
    $file = file_get_contents($filename);
    // Array delle righe del file
    $lines = explode("\n", $file);

    unset ($lines[0]);

    foreach ($lines as $value) {
      $data = str_getcsv($value, ';', '"');

      if (sizeof($data)<=1)
        continue;

      if ($data[5] == "Saldo iniziale") {
        $this->balance_start = (float) Util::getImporto($data[3]);
        continue;
      }

      if ($data[5] == "Saldo contabile") {
        $this->balance_end_real = (float) Util::getImporto($data[3]);
        continue;
      }

      $line = array();

      $date = \DateTime::createFromFormat('d/m/Y', $data[0]);
      $line["DATE"] = $date->format('Y-m-d');

      if ($data[2] == "")
        $line["AMT"] = (float) Util::getImporto($data[3]);
      else
        $line["AMT"] = (float) (Util::getImporto($data[2]) * -1);

      $line["DESCRIPTION"] = trim($data[5]);

      $this->lines[] = $line;

    }

  }

  protected function elaboraPdf($filename) {

    $parsedText = \Spatie\PdfToText\Pdf::getText($filename);

    if(!preg_match("/SALDO CONTABILE AL (.*):\n\nCONTO:/i", $parsedText, $matcher))
      return;

    $date = \DateTime::createFromFormat('d/m/Y', $matcher[1]);
    $day = $date->format('Y-m-d');
    $this->first_day_time = strtotime($day);

    $this->uploadPdf($filename);

  }

}

 ?>
