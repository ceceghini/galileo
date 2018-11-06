<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_adwords".
 *
 * @property int $id
 * @property string $keyword
 * @property int $click
 * @property int $impression
 */
class Adwords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'toner_adwords';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keyword', 'click', 'impression'], 'required'],
            [['click', 'impression'], 'integer'],
            [['keyword'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keyword' => 'Keyword',
            'click' => 'Click',
            'impression' => 'Impression',
        ];
    }
}
