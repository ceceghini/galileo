<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_source_product_modelli".
 *
 * @property int $id_product
 * @property int $id_modello
 *
 * @property TonerSourceModelli $modello
 * @property TonerSourceProduct $product
 */
class ProductModelli extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_product_modelli';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_product', 'id_modello'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'id_modello' => 'Id Modello',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModello()
    {
        return $this->hasOne(Modelli::className(), ['id' => 'id_modello']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_product']);
    }
}
