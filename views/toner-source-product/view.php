<?php

use yii\helpers\Html;
use kartik\tabs\TabsX;
use yii\helpers\Url;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use kartik\select2\Select2;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Source\Product */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$source = $model;

CrudAsset::register($this);

?>
<div class="product-view row">

  <h1><?= Html::encode($this->title) ?></h1>

  <div class="col-xs-5">

    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">
          <i class="glyphicon glyphicon-list"></i> Dati prodotto sorgente
        </h3>
        <div class="clearfix"></div>
      </div>

      <div class="panel-body">

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'sku',
                'title',
                'price',
                'color',
                'description',
                'qty',
                'source',
                'source_key',
                'elaborato:boolean',
                'disabled:boolean',
                'is_present:boolean',
                'url:url'
            ],
        ]); ?>

      </div>

    </div>

    <div id="ajaxCrudDatatable">

      <?= GridView::widget([
          'id'=>'crud-datatable-modelli',
          'dataProvider' => $dataProviderModelli,
          'pjax'=>true,
          'panel' => [
  					'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-list"></i> Modelli sorgenti collegati'
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
              },
            ],
            'nome',
            'serie',
            'marca',
            [
              'attribute' => 'id_modello',
              'class' => 'kartik\grid\EditableColumn',
              'refreshGrid' => true,
              'editableOptions' => function ($model) {

                return [
                  'displayValue' => isset($model->modello) ? $model->modello->nome : null,
                  'formOptions' => [
                    'action' => ['/toner-source-modelli/editable-update']
                  ],
                  'inputType' => \kartik\editable\Editable::INPUT_SELECT2,
                  'options' => [
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'initValueText' => isset($model->modello) ? $model->modello->nome : null,
                    'options' => [
                      "placeholder" => "seleziona il modello"
                    ],
                    'pluginOptions' => [
                      'ajax' => [
                          'url' => "/toner-modelli/find",
                          'dataType' => 'json',
                      ],
                      'allowClear' => true
                    ]
                  ]
                ];

              },
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{create}',
              'buttons' => [
                'create' => function ($url, $source, $key) use($model) {

                  return Html::a('<span class="glyphicon glyphicon-plus"></span>', $url, ['role'=>'modal-remote']);
                }
              ],
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["toner-modelli/$action", "id_source" => $model->id]);
              },
              "visibleButtons" => [
                "create" => function ($model, $key, $index) {
                  return !isset($model->id_modello);
                }
              ]
            ],
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

  <div class="col-xs-7">

    <?= GridView::widget([
        'id'=>'crud-datatable-prodotti',
        'dataProvider' => $dataProviderProdotti,
        'filterModel' => $searchModelProdotti,
        'pjax'=>true,
        'panel' => [
					'type' => 'primary',
          'heading' => '<i class="glyphicon glyphicon-list"></i> Prodotti collegati / collegabili'
        ],
        'toolbar'=> [],
        'summary' => '',
        'columns' => [
          [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{join}',
            'buttons' => [
              'join' => function ($url, $model, $key) use ($source){

                $state = $source->isJoined($model->id);

                if (!isset($state))
                  return Html::a("Collega", Url::to(["join", "id" => $source->id, "id_product" => $model->id]));

                switch($state) {
                    case 0:
                      return Html::a("Disabilita", Url::to(["unjoin", "id" => $source->id, "id_product" => $model->id]));
                      break;
                    case 1:
                      return Html::a("Abilita", Url::to(["join", "id" => $source->id, "id_product" => $model->id]));
                      break;
                    case 2:
                      return Html::a("Scollega", Url::to(["unjoin", "id" => $source->id, "id_product" => $model->id]));
                      break;
                }

              }
            ],
          ],
          [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'urlCreator' => function ($action, $model, $key, $index) {
              return Url::to(["toner-product/$action", "id" => $model->id]);
            },
          ],
          'sku',
          [
            'attribute' => 'state',
            'label' => "Stato",
            'value' => function ($model, $key, $index, $column) use ($source){

              return $source->joinState($model->id);

            }
          ],
          'marca.nome',
          'tipologia',
          'colore',
          'resa',
        ],
    ]); ?>

    <?= Html::a("Crea prodotto a partire dal prodotto sorgente", Url::to(["/toner-product/create", "id_source"=>$model->id]), ['role'=>'modal-remote', "class"=>"btn btn-danger"]); ?>
    <?= Html::a("Crea modelli a partire dal prodotto sorgente", Url::to(["/toner-source-product/create-modelli", "id"=>$model->id]), ["class"=>"btn btn-warning"]); ?>

    <br><br>

    <?php if($dataProviderProdottiSource): ?>

      <?= GridView::widget([
          'id' => 'prodotti2',
          'dataProvider' => $dataProviderProdottiSource,
          //'filterModel' => $searchModelProdotti,
          'panel' => [
            'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-list"></i> Elenco prodotti sorgenti che saranno collegati'
          ],
          'toolbar'=> [],
          'summary' => '',
          //'pjax'=>true,
          'columns' => [
            [
              'attribute' => "source",
              'group' => 'true'
            ],
            'source_key',
            'sku',
            'title',
            'price:currency',
            [
              'attribute' => "url",
              'format' => 'raw',
              'value' => function ($model, $key, $index, $column) {
                return Html::a('<span class="glyphicon glyphicon-link"></span>', Url::to($model->url), ["target" => "_new"]);
              }
            ]
          ],
      ]); ?>

    <?php endif; ?>

  </div>

</div>
