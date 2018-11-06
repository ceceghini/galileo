<?php

namespace app\models\Toner\Report;

use Yii;

/**
 * This is the model class for table "toner_report_price_sale".
 *
 * @property int $id
 * @property string $sku
 * @property string $tipologia
 * @property string $colore
 * @property string $resa
 * @property string $compatibile_prezzo
 * @property string $prezzo_avg
 * @property int $qty
 * @property string $total
 * @property string $margine
 * @property string $prezzo_avg2
 */
class PriceSale extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_report_price_sale';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'tipologia' => 'Tipologia',
            'colore' => 'Colore',
            'resa' => 'Resa',
            'compatibile_prezzo' => 'Compatibile Prezzo',
            'prezzo_avg' => 'Costo puntorigenera',
            'qty' => 'Qty',
            'total' => 'Total',
            'margine' => 'Margine',
            'prezzo_avg2' => 'Prezzo tuttocartucce',
        ];
    }

    /*public function getPrezzoNuovo() {

      if ($this->tipologia=="CARTUCCIA") {
        if ($this->prezzo_avg < 1)
          return $this->prezzo_avg * 4;
        else
          return $this->prezzo_avg * 2;
      }
      else {
        if ($this->prezzo_avg < 10)
          return $this->prezzo_avg * 2.5;
        elseif ($this->prezzo_avg < 35)
          return $this->prezzo_avg * 2;
        elseif ($this->prezzo_avg < 55)
          return $this->prezzo_avg * 1.5;
        else
          return $this->prezzo_avg * 1.1;
      }

      return 0;

    }*/

    public function getPrezzoMedioVendita() {

      return $this->total / $this->qty;

    }

    public function getScostamentoNuovo() {

      if ($this->getPrezzoMedioVendita()>0) {
          $scostamento = 1 - ($this->getPrezzoNuovo() / $this->getPrezzoMedioVendita());
          return $scostamento * -1;
      }
      else
        return 1;

    }

}
