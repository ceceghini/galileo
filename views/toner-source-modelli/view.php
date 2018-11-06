<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Source\Modelli */

$this->title = "{$model->sourceSerie->sourceMarca->nome} {$model->sourceSerie->nome} $model->nome";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="modelli-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nome',
            'serie',
            'marca',
            'photo',
            'source',
            'id_modello',
            'source_key',
            'elaborato:boolean',
            'url:url'
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProviderProduct,
        'panel' => [
          'type' => 'primary',
          'heading' => 'Prodotti collegati'
        ],
        'toolbar'=> [],
        'summary' => '',
        'showFooter' => false,
        'columns' => [
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view}',
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["toner-source-product/$action", "id" => $model->id]);
              }
            ],
            'sku',
            'title',
            'price',
            'color',
            //'description',
            'source_key',
            'elaborato:boolean',
            'disabled:boolean',
            'url:url'
        ],
    ]); ?>

</div>
