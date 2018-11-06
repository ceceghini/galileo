<?php

namespace app\models\Toner\Report;

use Yii;

/**
 * This is the model class for table "toner_report_price_scostamento".
 *
 * @property int $id
 * @property string $sku
 * @property string $source
 * @property string $prezzo_medio
 * @property string $prezzo_minimo
 * @property string $prezzo_massimo
 * @property int $numero
 * @property string $scostamento
 */
class PriceScostamento extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_report_price_scostamento';
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
            'source' => 'Source',
            'prezzo_medio' => 'Prezzo Medio',
            'prezzo_minimo' => 'Prezzo Minimo',
            'prezzo_massimo' => 'Prezzo Massimo',
            'numero' => 'Numero',
            'scostamento' => 'Scostamento',
        ];
    }
}
