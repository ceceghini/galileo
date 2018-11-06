<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_serie".
 *
 * @property integer $id
 * @property string $nome
 * @property integer $id_marca
 *
 * @property TonerModelli[] $tonerModellis
 * @property TonerMarche $idMarca
 */
class Serie extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_serie';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome', 'id_marca'], 'required'],
            [['id_marca'], 'integer'],
            [['nome'], 'string', 'max' => 255],
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
            'id_marca' => 'Marca',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarca()
    {
        return $this->hasOne(Marche::className(), ['id' => 'id_marca']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelli()
    {
        return $this->hasMany(Modelli::className(), ['id_serie' => 'id']);
    }

}
