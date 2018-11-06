<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string $key
 * @property string $level
 * @property string $text
 * @property string $url
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'level', 'text'], 'required'],
            [['title', 'level', 'text', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'level' => 'Level',
            'text' => 'Text',
            'url' => 'Url',
        ];
    }

    public static function add($title, $level, $text, $url) {

      $message = \app\models\Message::findOne([
        "title" => $title,
        "text" => $text
      ]);

      if ($message) {
        $message->date = date("Y-m-d h");
      }
      else {
        $message = new Message();
        $message->title = $title;
        $message->level = $level;
        $message->text = $text;
        $message->url = $url;
        $message->date = date("Y-m-d h");
      }

      $message->save();

    }

}
