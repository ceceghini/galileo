<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Source\Serie */

$this->title = "{$model->sourceMarca->nome} {$model->nome}";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="serie-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nome',
            'sourceMarca.nome',
            'source',
            [
              'attribute' => "id_serie",
              'value' => "serie.nome"
            ],
            'source_key',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProviderModelli,
        'panel' => [
          'type' => 'primary',
          'heading' => 'Modelli collegati'
        ],
        'toolbar'=> [],
        'summary' => '',
        'showFooter' => false,
        'columns' => [
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view}',
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["toner-source-modelli/$action", "id" => $model->id]);
              }
            ],
            'nome',
            'serie',
            'marca',
            'url:url',
        ],
    ]); ?>

</div>
