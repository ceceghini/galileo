<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_source_modelli".
 *
 * @property int $id
 * @property string $nome
 * @property string $serie
 * @property string $marca
 * @property int $id_source_serie
 * @property string $photo
 * @property string $data
 * @property string $source
 * @property int $id_modello
 *
 * @property TonerModelli $modello
 * @property TonerSourceSerie $sourceSerie
 */
class Modelli extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_modelli';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
      return [
        [['id_modello'], 'safe'],
      ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'serie' => 'Serie',
            'marca' => 'Marca',
            'id_source_serie' => 'Source Serie',
            'photo' => 'Photo',
            'data' => 'Data',
            'source' => 'Source',
            'id_modello' => 'Modello collegato',
            'elaborato' => 'Elaborato'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModello()
    {
        return $this->hasOne(\app\models\Toner\Modelli::className(), ['id' => 'id_modello']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceSerie()
    {
        return $this->hasOne(Serie::className(), ['id' => 'id_source_serie']);
    }

    /*public function getProductLink()
    {
        return $this->hasOne(ProductModelli::className(), ['id' => 'id_source_serie']);
    }*/

    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'id_product'])->viaTable('toner_source_product_modelli', ['id_modello' => 'id']);
    }
}
