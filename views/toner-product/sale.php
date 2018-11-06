<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\components\Util;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TonerProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Vendite prodotti';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="toner-product-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php $massive = Html::a('Nuovo prodotto', "create", ['class' => 'btn btn-success']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {update}',
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to([$action, "id" => $model->product->id]);
              }
            ],
            [
              'attribute' => 'sku',
              'value' => "product.sku"
            ],
            "product.marca.nome",
            [
              'attribute' => "id_refill",
              'value' => 'product.id_refill',
              'filter' => Html::activeDropDownList($searchModel, "id_refill", Util::getYesNo(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'attribute' => "product.id_pr",
              'filter' => Html::activeDropDownList($searchModel, "id_pr", Util::getYesNo(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'product.prezzo_compatibile:currency',
            'product.prezzo_supplies:currency',
            'period',
            'tipologia',
            'qty',
            'total:currency'
        ],
        'showPageSummary' => false,
        'panel' => [
    		  'type'=>GridView::TYPE_DEFAULT,
        ],
        'toolbar' => [
      		'content' => $massive,
          	'',
          	//'{toggleData}'
      	],
    ]); ?>
</div>
