<?php

namespace app\models\Eurocali;

use Yii;

/**
 * This is the model class for table "eurocali_product_category".
 *
 * @property int $id_product
 * @property int $id_category
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eurocali_product_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_product', 'id_category'], 'required'],
            [['id_product', 'id_category'], 'integer'],
            [['id_product', 'id_category'], 'unique', 'targetAttribute' => ['id_product', 'id_category']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_product' => 'Id Product',
            'id_category' => 'Id Category',
        ];
    }
}
