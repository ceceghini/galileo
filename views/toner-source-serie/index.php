<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Toner\Source\Marche;
use app\components\Util;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\Source\SerieSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Serie';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="serie-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'nome',
            [
              'attribute' => 'id_source_marca',
              'value' => 'sourceMarca.nome',
              'filter' => Html::activeDropDownList($searchModel, "id_source_marca", yii\helpers\ArrayHelper::map(Marche::find()->asArray()->all(), 'id', 'nome'), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'class' => 'kartik\grid\EditableColumn',
              'editableOptions'=> [
                'formOptions' => [
                  'action' => ['/toner-source-serie/set-serie']
                ],
              ],
              'attribute' => 'id_serie',
              'value' => 'serie.nome',
              'filter' => Html::activeDropDownList($searchModel, "id_serie", Util::getFormatBoolean(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'attribute' => "source",
              'filter' => Html::activeDropDownList($searchModel, "source", yii\helpers\ArrayHelper::map(\app\models\Toner\Source\Marche::find()->asArray()->all(), 'source', 'source'), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'source_key',
            'is_present:boolean',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{create}',
              'buttons' => [
                'create' => function ($url, $model, $key) {
                  return Html::a("create", Url::to($url));
                }
              ],
            ],
        ],
    ]); ?>
</div>
