<?php

namespace app\models\Toner\Report;

use Yii;

class DaEliminare extends \yii\base\Model
{

  public $skus;

  /**
   * @inheritdoc
   */
  public function rules()
  {
      return [
          [['skus'], 'safe'],
      ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
      return [
          'skus' => 'Codici sku da eliminare',
      ];
  }

}
