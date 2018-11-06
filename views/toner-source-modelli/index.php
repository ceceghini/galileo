<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\components\Util;
use yii\helpers\Url;
use johnitvn\ajaxcrud\CrudAsset;
use johnitvn\ajaxcrud\BulkButtonWidget;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\Source\ModelliSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Modelli';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="modelli-index">

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
            'nome',
            'serie',
            'marca',
            [
              /*'class' => 'kartik\grid\EditableColumn',
              'editableOptions'=> function ($model, $key, $index) {
                return [
                  'formOptions' => ['action' => ['/toner-source-serie/set-serie']]
                ];
              },*/
              'attribute' => 'id_modello',
              'value' => 'modello.nome',
            ],
            [
              'attribute' => "source",
              'filter' => Html::activeDropDownList($searchModel, "source", yii\helpers\ArrayHelper::map(\app\models\Toner\Source\Marche::find()->asArray()->all(), 'source', 'source'), ["class" => 'form-control', 'prompt' => '...'])
            ],
            'source_key',
            'elaborato:boolean',
            'is_present:boolean'
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
          'content' => Html::a("Modelli non collegati", Url::to(["index", "filter"=>"noncollegati"]), ["class" => "btn btn-info"])
        ]
    ]); ?>
</div>


<?php Modal::begin([
  "id"=>"ajaxCrudModal",
  "footer"=>"",// always need it for jquery plugin
  "options" => ['tabindex' => false,],
])?>
<?php Modal::end(); ?>
