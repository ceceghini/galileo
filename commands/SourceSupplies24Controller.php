<?php

namespace app\commands;

use app\components\Util;
use yii\console\Controller;

class SourceSupplies24Controller extends Controller
{

  private $url = "https://www.supplies24.it/Pricelist/18678/U04T4TJ6UUW9SFEQUROX63M";

  protected $source = "supplies24";

  private $marche = array(
      "Brother" => 1,
      "Canon" => 1,
      "Dell" => 1,
      "Epson" => 1,
      "HP" => 1,
      "IBM" => 1,
      "Konica Minolta" => 1,
      "Kyocera" => 1,
      "Lexmark" => 1,
      "Mita" => 1,
      "OKI" => 1,
      "Olivetti" => 1,
      "Panasonic" => 1,
      "Philips" => 1,
      "Ricoh" => 1,
      "Samsung" => 1,
      "Tally" => 1,
      "Xerox" => 1
  );

  function actionProcessMain() {

    \Yii::$app->db->createCommand("update toner_source_product set is_present = 0 where source = '$this->source'")->query();

    $data = file_get_contents($this->url);
		$csv = explode("\n", $data);

    foreach ($csv as $k=>$row) {

      if ($row) {

        $data = str_getcsv($row, ";");

        if (isset($this->marche[$data[1]])) {

          $model = \app\models\Toner\Source\Product::findOne(["source_key" => $data[0], "source" => $this->source]);
          $save = false;
          if (!$model) {
            $model = new \app\models\Toner\Source\Product();
            $model->source = $this->source;
            $model->source_key = $data[0];
            $save = true;
          }
          if ($model->sku != $data[2]) {
            $model->sku = $data[2];
            $save = true;
          }
          if ($model->title != strtoupper($data[4])) {
            $model->title = strtoupper($data[4]);
            $save = true;
          }
          if ($model->description != strtoupper($data[5])) {
            $model->description = strtoupper($data[5]);
            $save = true;
          }
          if ($model->price != $data[6]) {
            $model->price = $data[6];
            $save = true;
          }
          if ($model->qty != $data[7]) {
            $model->qty = $data[7];
            $save = true;
          }
          if ($model->html != $data[3]) {
            $model->html = $data[3];
            $save = true;
          }

          $model->is_present = 1;
          $model->elaborato = 1;

          if (!$model->save())
            print_r($model->getErrors());

        }

      }

      /*$sql = "delete from toner_source_product
 where source = '$this->source'
   and is_present = 0
   and not exists (select 1 from toner_source_product_join j where toner_source_product.id = j.id_source_product and j.disabled <> 1)";

    \Yii::$app->db->createCommand($sql)->query();*/

    }

  }

  function disableProduct() {
    \Yii::$app->db->createCommand("update toner_source_product set disabled = 1 where source = '$this->source' and sku like '%MCVP%'")->execute();
  }

  /**
  -- verifica prodotti non presenti con le vendite
  select p.sku, v.qty
  from toner_source_product s
    join toner_source_product_join j on s.id = j.id_source_product
    join toner_product p on j.id_product = p.id
    left outer join toner_product_sale v on v.id_product = p.id and v.tipologia = 'ORIGINALE'
 where source = 'supplies24'
   and is_present = 0
  **/


}
