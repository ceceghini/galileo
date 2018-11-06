<?php

namespace app\models\Eurocali;

use Yii;

/**
 * This is the model class for table "supplier_product".
 *
 * @property int $id
 * @property string $url
 * @property string $brand
 * @property int $present
 * @property int $elaborato
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eurocali_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
      return [
          [['url', 'brand'], 'required'],
          [['present', 'elaborato'], 'integer'],
          [['price'], 'number'],
          [['description', 'html'], 'string'],
          [['url'], 'string', 'max' => 245],
          [['brand', 'title'], 'string', 'max' => 100],
          [['short_description'], 'string', 'max' => 1000],
          [['json_data'], 'string', 'max' => 3000],
          [['url'], 'unique'],
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'brand' => 'Brand',
            'present' => 'Present',
            'elaborato' => 'Elaborato',
        ];
    }
}
