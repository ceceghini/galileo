<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\Util;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\Source\MarcheSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Marche sorgenti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="marche-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'nome',
            [
              'attribute' => "id_marca",
              "value" => 'marca.nome',
              'filter' => Html::activeDropDownList($searchModel, "id_marca", Util::getFormatBoolean(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'attribute' => "source",
              'filter' => Html::activeDropDownList($searchModel, "source", yii\helpers\ArrayHelper::map(\app\models\Toner\Source\Marche::find()->asArray()->all(), 'source', 'source'), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'source_key',
        ],
    ]); ?>
</div>
