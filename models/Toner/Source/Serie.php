<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_source_serie".
 *
 * @property int $id
 * @property string $nome
 * @property int $id_source_marca
 * @property string $data
 * @property string $source
 * @property string $source_key
 * @property int $id_serie
 *
 */
class Serie extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_serie';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
          [['id_serie'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Serie',
            'id_source_marca' => 'Source Marca',
            'data' => 'Data',
            'source' => 'Source',
            'id_serie' => 'Serie collegata',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasOne(\app\models\Toner\Serie::className(), ['id' => 'id_serie']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceMarca()
    {
        return $this->hasOne(Marche::className(), ['id' => 'id_source_marca']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelli()
    {
        return $this->hasMany(Modelli::className(), ['id_source_serie' => 'id']);
    }
}
