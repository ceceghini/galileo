<?php

namespace app\components\Odoo\AccountBank;

use app\components\Odoo\AccountBank;
use app\components\Util;

class CartaSi extends AccountBank {

  protected $_subpath = "cartasi";

  protected $journal_id = 14;
  protected $prefix_name = "CARTASI";
  protected $pdf_name = "cartasi";

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

      $line = array();

      $date = \DateTime::createFromFormat('d/m/Y', $data[1]);
      $line["DATE"] = $date->format('Y-m-d');

      $line["AMT"] = ((float) Util::getImporto($data[5])) * -1;

      $line["DESCRIPTION"] = $data[3] . " " . $data[4];

      $this->balance_end_real += $line["AMT"];

      $this->lines[$line["DATE"]."-".$data[2]] = $line;

    }

    if ($this->balance_end_real > 77.47) {

      $line["DATE"] = date("Y-m-t", $line["DATE"]);
      $line["AMT"] = -2;
      $line["DESCRIPTION"] = "Imposta di bollo";
      $this->balance_end_real += $line["AMT"];
      $this->lines[$line["DATE"]."-zzz"] = $line;

    }

    ksort($this->lines);

    //print_r ($lines);

  }

  protected function elaboraPdf($filename) {

    $parsedText = \Spatie\PdfToText\Pdf::getText($filename);

    print $parsedText;

    if(!preg_match("/TOTALE ADDEBITO SUL SUO C\/C\nDebito residuo al (.*)\n/i", $parsedText, $matcher))
      return;

    $date = \DateTime::createFromFormat('d/m/Y', $matcher[1]);
    $day = $date->format('Y-m-d');
    $this->first_day_time = strtotime($day);

    $this->uploadPdf($filename);

  }

}

 ?>
