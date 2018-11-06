<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;
use app\components\Util;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\TonerProduct */

$this->title = $model->sku." [$model->colore]";
if ($model->marca)
  $this->title .= " [".$model->marca->nome."]";
$this->params['breadcrumbs'][] = ['label' => 'Toner Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="toner-product-view">

    <h1 class="col-sm-12"><?= Html::encode($this->title) ?></h1>

    <div class="col-xs-5">

      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="glyphicon glyphicon-list"></i> Dati prodotto
          </h3>
          <div class="clearfix"></div>
        </div>

        <div class="panel-body">

          <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'sku',
                [
                  'attribute' => "id_marca",
                  'value' => isset($model->marca) ? $model->marca->nome : "MARCA MANCANTE",
                  'label' => "Marca"
                ],
                'ean',
                'enabled:boolean',
                'tipologia',
                'colore',
                'resa',
                'id_verdestampa',
                'url:url',
                'compatibile:boolean',
                'compatibile_prezzo:currency',
                'compatibile_prezzo_source',
                'compatibile_prezzo_tonerper:currency',
                'compatibile_prezzo_ecolors:currency',
                'compatibile_prezzo_updated',
                [
                  'attribute' => 'compatibile_url_foto',
                  'format' => ['image',['width'=>'200']],
                ],
                'originale:boolean',
                'originale_prezzo:currency',
                [
                  'attribute' => 'originale_url_foto',
                  'format' => ['image',['width'=>'200']],
                ],
                'originale_ean',
                'part_number'
            ],
          ]) ?>

        </div>

      </div>

      <?= Html::a("Crea prodotto simile", Url::to(["/toner-product/create", "id_prodotto"=>$model->id]), ['role'=>'modal-remote', "class"=>"btn btn-danger"]); ?>

    </div>

    <div class="col-xs-7">

      <?= GridView::widget([
          'dataProvider' => $dataProviderVendite,
          'panel' => [
  					'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-euro"></i> Vendite'
          ],
          'toolbar'=> [],
          'summary' => '',
          'columns' => [
            'tipologia',
            'period',
            'qty',
            'total:currency'
          ],
      ]); ?>

      <?= GridView::widget([
          'dataProvider' => $dataProviderVS,
          'panel' => [
  					'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-euro"></i> Prezzi verdestampa'
          ],
          'toolbar'=> [],
          'summary' => '',
      ]); ?>

      <?= GridView::widget([
          'id' => 'prodotti2',
          'dataProvider' => $dataProviderProdotti,
          'filterModel' => $searchModelProdotti,
          'panel' => [
      			'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-list"></i> Elenco prodotti sorgenti collegati'
          ],
          'toolbar'=> [],
          'summary' => '',
          //'pjax'=>true,
          'columns' => [
            [
              'attribute' => "source",
              'filter' => Html::activeDropDownList($searchModelProdotti, "source", Util::getSource(), ["class" => 'form-control', 'prompt' => '...']),
              'group' => 'true'
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{join}',
              'buttons' => [
                'join' => function ($url, $source, $key) use ($model){

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
              'urlCreator' => function($action, $model, $key, $index) {
                return Url::to(["toner-source-product/".$action,'id'=>$model["id"]]);
              }
            ],
            'source_key',
            'sku',
            [
              'attribute' => 'state',
              'label' => "Stato",
              'value' => function ($source, $key, $index, $column) use ($model){

                return $source->joinState($model->id);

              }
            ],
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

      ]);

      ?>

      <?= GridView::widget([
          'dataProvider' => $dataProviderModelli,
          'filterModel' => $searchModelModelli,
          'panel' => [
  					'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-list"></i> Modelli collegati'
          ],
          'toolbar'=> [],
          'summary' => '',
          'columns' => [
              [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'urlCreator' => function($action, $model, $key, $index) {
                  return Url::to(["toner-modelli/".$action,'id'=>$model["id"]]);
                }
              ],
              [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{join}',
                'buttons' => [
                  'join' => function ($url, $modello, $key) use ($model){

                    $state = $modello->isJoined($model->id);

                    if (!isset($state))
                      return Html::a("Collega", Url::to(["join-modello", "id" => $model->id, "id_modello" => $modello->id]));

                    switch($state) {
                        case 0:
                          return Html::a("Disabilita", Url::to(["unjoin-modello", "id" => $model->id, "id_modello" => $modello->id]));
                          break;
                        case 1:
                          return Html::a("Abilita", Url::to(["join-modello", "id" => $model->id, "id_modello" => $modello->id]));
                          break;
                        case 2:
                          return Html::a("Scollega", Url::to(["unjoin-modello", "id" => $model->id, "id_modello" => $modello->id]));
                          break;
                    }

                  }
                ],
              ],
              'nome',
              'serie',
              'marca',
              'tipologia'
          ],
      ]); ?>

    </div>

</div>

<?php Modal::begin([
  "id"=>"ajaxCrudModal",
  "footer"=>"",// always need it for jquery plugin
  "options" => ['tabindex' => false,],
])?>
<?php Modal::end(); ?>
