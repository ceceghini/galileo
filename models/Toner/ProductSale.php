<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_product_sale".
 *
 * @property int $id_product
 * @property string $tipologia
 * @property string $period
 * @property int $qty
 * @property string $total
 *
 * @property TonerProduct $product
 */
class ProductSale extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_product_sale';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'tipologia', 'period', 'qty', 'total'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'tipologia' => 'Tipologia',
            'period' => 'Period',
            'qty' => 'Qty',
            'total' => 'Total',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }

    public function getPrezzoMedioVendita() {

      return $this->total / $this->qty;

    }

}
