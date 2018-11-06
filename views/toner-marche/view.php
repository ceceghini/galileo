<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Marche */

$this->title = $model->nome;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="marche-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'nome',
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
                return Url::to(["toner-serie/$action", "id" => $model->id]);
              }
            ],
            'nome',
        ],
    ]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProviderSource,
        'panel' => [
          'type' => 'primary',
          'heading' => 'Elenco marche sorgenti collegate'
        ],
        'toolbar'=> [],
        'summary' => '',
        'showFooter' => false,
        'columns' => [
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view}',
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["toner-source-marche/$action", "id" => $model->id]);
              }
            ],
            'source',
            'source_key',
            'nome',
        ],
    ]); ?>

</div>
