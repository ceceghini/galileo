<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_product_modelli".
 *
 * @property integer $id_prodotto
 * @property integer $id_modello
 *
 * @property TonerModelli $idModello
 * @property TonerProduct $idProdotto
 */
class ProductModelli extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_product_modelli';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_prodotto', 'id_modello'], 'required'],
            [['id_prodotto', 'id_modello'], 'integer'],
            [['id_modello'], 'exist', 'skipOnError' => true, 'targetClass' => Modelli::className(), 'targetAttribute' => ['id_modello' => 'id']],
            [['id_prodotto'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['id_prodotto' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_prodotto' => 'Id Prodotto',
            'id_modello' => 'Modello',
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
    public function getProdotto()
    {
        return $this->hasOne(Product::className(), ['id' => 'id_prodotto']);
    }

}
