<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_marche_source".
 *
 * @property int $id
 * @property string $nome
 * @property string $data
 * @property string $source
 * @property string $source_key
 * @property int $id_marca
 *
 */
class Marche extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_marche';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
          [["nome", "source", "source_key"], "required"]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Marca',
            'data' => 'Data',
            'source' => 'Source',
            'id_marca' => 'Marca collegata'
        ];
    }

    public function beforeSave($insert) {

      if (!$this->id_marca) {
        $marca = \app\models\Toner\Marche::findOne(["nome" => $this->nome]);
        if ($marca) {
            $this->id_marca = $marca->id;
        }
      }

      return parent::beforeSave($insert);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarca()
    {
        return $this->hasOne(\app\models\Toner\Marche::className(), ['id' => 'id_marca']);
    }

}
