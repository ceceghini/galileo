<?php

namespace app\models\Toner\Report;

use Yii;

/**
 * This is the model class for table "toner_report_price_verdestampa2".
 *
 * @property string $sku
 * @property string $tipologia
 * @property string $colore
 * @property string $resa
 * @property string $compatibile_prezzo
 * @property string $negozio
 * @property string $prezzo
 */
class PriceVerdestampa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'toner_report_price_verdestampa2';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sku', 'compatibile_prezzo', 'negozio', 'prezzo'], 'required'],
            [['compatibile_prezzo', 'prezzo'], 'number'],
            [['sku'], 'string', 'max' => 32],
            [['tipologia', 'colore', 'resa', 'negozio'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sku' => 'Sku',
            'tipologia' => 'Tipologia',
            'colore' => 'Colore',
            'resa' => 'Resa',
            'compatibile_prezzo' => 'Compatibile Prezzo',
            'negozio' => 'Negozio',
            'prezzo' => 'Prezzo',
        ];
    }
}
