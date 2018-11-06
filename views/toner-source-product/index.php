<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\components\Util;
use yii\helpers\Url;
use johnitvn\ajaxcrud\CrudAsset;
use johnitvn\ajaxcrud\BulkButtonWidget;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\Source\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Prodotti sorgenti';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="product-index">

    <?= GridView::widget([
        'id'=>'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true,
        'columns' => [
            [
              'class' => 'kartik\grid\CheckboxColumn',
              'width' => '20px',
            ],
            [
              'class' => 'kartik\grid\ActionColumn',
              'template' => '{view} {delete}',
              'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete',
                          'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                          'data-request-method'=>'post',
                          'data-toggle'=>'tooltip',
                          'data-confirm-title'=>'Are you sure?',
                          'data-confirm-message'=>'Are you sure want to delete this item'],
            ],

            'sku',
            'title',
            'price',
            'color',
            //'description',
            [
              'attribute' => "source",
              'filter' => Html::activeDropDownList($searchModel, "source", Util::getSource(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'source_key',
            'elaborato:boolean',
            [
              'attribute' => "disabled",
              'filter' => Util::getFormatBoolean(),
              'format' => 'html',
              "value" => function ($model, $key, $index, $column) {
                return Html::a(($model->disabled ? "Sì" : "No"), Url::to(["toner-source-product/enable-disable", "id" => $model->id]));
              }
            ],
            'is_present:boolean',
            'qty',
            'OtherSource'
        ],
        'panel' => [
          'type' => 'primary',
          'heading' => '<i class="glyphicon glyphicon-list"></i> '.Html::encode($this->title)." [$filter]",
          'before'=>'',
          'after'=>BulkButtonWidget::widget([
            'buttons'=>Html::a('<i class="glyphicon glyphicon-trash"></i>&nbsp; Delete All',
              ["bulk-delete"] ,
              [
                "class"=>"btn btn-danger btn-xs",
                'role'=>'modal-remote-bulk',
                'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                'data-request-method'=>'post',
                'data-confirm-title'=>'Are you sure?',
                'data-confirm-message'=>'Are you sure want to delete this item'
              ]),
          ]).
          '<div class="clearfix"></div>',
        ],
        'toolbar' => [
          'content' => Html::a("Prodotti non collegati", Url::to(["index", "filter"=>"noncollegati"]), ["class" => "btn btn-info"])
        ]
    ]); ?>
</div>

<?php Modal::begin([
  "id"=>"ajaxCrudModal",
  "footer"=>"",// always need it for jquery plugin
  "options" => ['tabindex' => false,],
])?>
<?php Modal::end(); ?>
