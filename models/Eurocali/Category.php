<?php

namespace app\models\Eurocali;

use Yii;

/**
 * This is the model class for table "supplier_category".
 *
 * @property int $id
 * @property string $title
 * @property string $url
 * @property string $description
 * @property int $present
 * @property int $elaborato
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eurocali_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
      return [
          [['title', 'url'], 'required'],
          [['present', 'elaborato', 'id_parent'], 'integer'],
          [['title', 'url', 'img'], 'string', 'max' => 100],
          [['description'], 'string', 'max' => 3000],
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'url' => 'Url',
            'description' => 'Description',
            'present' => 'Present',
            'elaborato' => 'Elaborato',
        ];
    }
}
