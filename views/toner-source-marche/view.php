<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Source\Marche */

$this->title = "$model->nome [$model->source]";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="marche-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nome',
            'source',
            'source_key',
            [
              'attribute' => "id_marca",
              'value' => "marca.nome"
            ],
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProviderSerie,
        'panel' => [
          'type' => 'primary',
          'heading' => 'Serie collegate'
        ],
        'toolbar'=> [],
        'summary' => '',
        'showFooter' => false,
        'columns' => [
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view}',
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["toner-source-serie/$action", "id" => $model->id]);
              }
            ],
            'nome',
            [
              'attribute' => 'id_serie',
              'value' => 'serie.nome',
            ],
            'source_key',
        ],
    ]); ?>

</div>
