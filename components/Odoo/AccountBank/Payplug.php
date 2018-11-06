<?php

namespace app\components\Odoo\AccountBank;

use app\components\Odoo\AccountBank;
use app\components\Util;

class Payplug extends AccountBank {

  protected $_subpath = "payplug";

  protected $charge_partner_id = 7256;
  protected $charge_name = "Commissioni payplug";
  protected $journal_id = 12;
  protected $prefix_name = "PAYPLUG";

  public function elaboraFile($filename) {

    // Recupero il contenuto del file
    $file = file_get_contents($filename);
    // Conversione del contenuto del file
    $result = mb_convert_encoding($file , 'UTF-8' , 'UTF-16LE');
    // Array delle righe del file
    $lines = explode("\n", $result);

    unset ($lines[0]);
    unset ($lines[1]);

    // Calcolo banlance start
    $value = $lines[sizeof($lines)];
    $data = str_getcsv($value, ',', '"');
    $this->balance_start = (float)Util::getImporto($data[5]) - (float)Util::getImporto($data[4]);

    // Calcolo balance end
    $value = $lines[2];
    $data = str_getcsv($value, ',', '"');
    $this->balance_end_real = (float)Util::getImporto($data[5]);

    // Loop fra le righe del file
    foreach ($lines as $value) {
      $data = str_getcsv($value, ',', '"');

      if ($data[0]=="")
        continue;

      if ($data[2]=="Bonifico") {
        $this->balance_end_real -= (float) Util::getImporto($data[4]);
        continue;
      }

      $line = array();

      $line["DATE"] = substr($data[1], 0, 10);
      $line["AMT"] = (float) Util::getImporto($data[4]);
      $line["TRXID"] = $data[10];
      $line["DESCRIPTION"] = $data[3];

      $charge = (float)Util::getImporto($data[6]);
      $this->charge_amt += $charge * -1;
      //$this->balance_end_real += $line["AMT"];

      $this->lines[$data[0]] = $line;

    }

    $this->balance_end_real += $this->charge_amt;
    ksort($this->lines);

    //echo $this->balance_start."\n";
    //print $this->balance_end_real."\n";

  }

}

 ?>
