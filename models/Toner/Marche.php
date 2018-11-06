<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_marche".
 *
 * @property int $id
 * @property string $nome
 * @property int $id_verdestampa
 *
 * @property TonerSourceMarche[] $tonerSourceMarches
 */
class Marche extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_marche';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome', 'id_verdestampa'], 'required'],
            [['id_verdestampa'], 'integer'],
            [['nome'], 'string', 'max' => 255],
            [['nome'], 'unique'],
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
            'id_verdestampa' => 'Id Verdestampa',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeries()
    {
        return $this->hasMany(Serie::className(), ['id_marca' => 'id']);
    }

}
