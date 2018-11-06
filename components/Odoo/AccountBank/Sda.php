<?php

namespace app\components\Odoo\AccountBank;

use app\components\Odoo\AccountBank;
use app\components\Util;

class Sda extends AccountBank {

  protected $_subpath = "sda";

  protected $charge_partner_id = null;
  protected $charge_name = null;
  protected $journal_id = 13;
  protected $prefix_name = "SDA";
  protected $pdf_name = "sda";

  public function elaboraFile($filename) {

    // Recupero il contenuto del file
    $file = file_get_contents($filename);
    // Array delle righe del file
    $lines = explode("\n", $file);

    unset ($lines[0]);

    foreach ($lines as $value) {
      $data = str_getcsv($value, ';', '"');

      if ($data[0] == "TOTALE") {
        $this->balance_end_real = (float) Util::getImporto($data[1]);
        continue;
      }

      if (sizeof($data)<8)
        continue;

      if ($data[0] == "CON" || $data[0] == "ABM") {
        continue;
      }

      $line = array();

      $date = \DateTime::createFromFormat('d/m/Y', $data[3]);
      $line["DATE"] = $date->format('Y-m-d');

      $line["AMT"] = (float) Util::getImporto($data[5]);

      $line["DESCRIPTION"] = $data[0]." - ".$data[4]." - ".$data[2];

      $this->lines[] = $line;

    }

  }

  protected function elaboraPdf($filename) {

  }

  protected function getLastDay() {

    $n = sizeof($this->lines);                // n° di rige
    $last_day = $this->lines[$n-1]["DATE"];   // La data dell'ultima riga

    return $last_day;

  }

  protected function getName() {

    return "{$this->prefix_name} - [".$this->getLastDay()."]";

  }

}

 ?>
