<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_source_url".
 *
 * @property int $id
 * @property string $url
 * @property int $elaborato
 * @property string $source
 */
class Url extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_url';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'source'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'elaborato' => 'Elaborato',
            'source' => 'Source',
        ];
    }
}
