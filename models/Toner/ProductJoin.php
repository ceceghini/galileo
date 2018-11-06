<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_source_product_join".
 *
 * @property int $id_product
 * @property int $id_source_product
 *
 * @property TonerSourceProduct $sourceProduct
 * @property TonerProduct $product
 */
class ProductJoin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_product_join';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'id_source_product'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'id_source_product' => 'Id Source Product',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceProduct()
    {
        return $this->hasOne(\app\models\Toner\Source\Product::className(), ['id' => 'id_source_product']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }
}
