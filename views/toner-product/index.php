<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Toner\Product;
use app\components\Util;
use app\models\Toner\Marche;
use yii\helpers\Url;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TonerProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Prodotti abilitati [Toner e cartucce]';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="toner-product-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php $massive = Html::a('Nuovo prodotto', "create", ['class' => 'btn btn-success']); ?>

    <div id="ajaxCrudDatatable">

    <?= GridView::widget([
        'id'=>'crud-datatable-prodotti',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true,
        'columns' => [
            [
              'class' => 'kartik\grid\ActionColumn',
              'template' => '{view} {update} {delete}',
              'headerOptions' => ['style' => 'width:80px'],
              'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
            ],
            'sku',
            'manuale:boolean',
            [
              'attribute' => 'enabled',
              'format' => "boolean",
              'filter' => Html::activeDropDownList($searchModel, "enabled", Util::getFormatBoolean(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'attribute' => 'id_marca',
              'value' => 'marca.nome',
              'filter' => Html::activeDropDownList($searchModel, "id_marca", yii\helpers\ArrayHelper::map(Marche::find()->asArray()->all(), 'id', 'nome'), ["class" => 'form-control', 'prompt' => '...'])
            ],
            [
              'attribute' => 'tipologia',
              'filter' => Html::activeDropDownList($searchModel, "tipologia", \app\models\Toner\Product::getTipologie(true), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'colore',
            'resa',
            'compatibile:boolean',
            [
              'attribute' => "compatibile_prezzo",
              'label' => 'Prezzo',
              'format' => 'currency'
            ],
            'originale:boolean',
            [
              'attribute' => "originale_prezzo",
              'label' => 'Prezzo',
              'format' => 'currency'
            ],
            'id_verdestampa'
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

    <?php Modal::begin([
      "id"=>"ajaxCrudModal",
      "footer"=>"",// always need it for jquery plugin
      "options" => ['tabindex' => false,],
    ])?>
    <?php Modal::end(); ?>

</div>
