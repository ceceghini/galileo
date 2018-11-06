<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_product_prezzi".
 *
 * @property int $id_product
 * @property string $negozio
 * @property string $prezzo
 */
class ProductPrezzi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'toner_product_prezzi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_product', 'negozio', 'prezzo'], 'required'],
            [['id_product'], 'integer'],
            [['prezzo'], 'number'],
            [['negozio'], 'string', 'max' => 255],
            [['id_product', 'negozio'], 'unique', 'targetAttribute' => ['id_product', 'negozio']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'negozio' => 'Negozio',
            'prezzo' => 'Prezzo',
        ];
    }
}
