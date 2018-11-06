<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Toner\Adwords;

class AdwordsController extends Controller {

  public function actionImport() {

    $path = "/opt/files/adwords/";

    foreach (new \DirectoryIterator($path) as $f) {

      if($f->isDot()) continue;
      if ($f->getFilename()==".DAV") continue;

      $this->elaboraFile($f->getRealPath());

    }

  }

  private function elaboraFile($filename) {

    $file = file_get_contents($filename);

    $lines = explode("\n", $file);
    unset($lines[0]);
    unset($lines[1]);
    unset($lines[2]);

    foreach ($lines as $value) {

      $data = str_getcsv($value, ',', '"');

      if (sizeof($data)<=1)
        continue;

      $adwords = Adwords::find()
        ->andWhere([
          "keyword" => $data[0]
        ])->one();

      if (!$adwords) {
        $adwords = new Adwords();
        $adwords->keyword = $data[0];
      }

      $adwords->click = $data[4];
      $adwords->impression = $data[5];
      if(!$adwords->save()) {
        print_r($adwords->getErrors());
        //print_r($data);
      }

    }

    //unlink($filename);

  }

}

?>
