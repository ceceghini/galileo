<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\TonerModelli */

$this->title = "$model->marca $model->serie $model->nome";
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="toner-modelli-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
      <div class="col-sm-10">

        <p>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        </p>

        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'nome',
                'serie',
                'tipologia',
                'enabled:boolean',
                'marca',
            ],
        ]) ?>

      </div>
      <div class="col-sm-2">
        <img src="<?= $model->photo ?>">
      </div>
    </div>

    <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id'=>'crud-datatable-prodotti',
        'dataProvider' => $dataProviderProduct,
        'pjax'=>true,
        'summary' => '',
        'responsive' => true,
				'panel' => [
					'type' => 'primary',
          'heading' => '<i class="glyphicon glyphicon-list"></i> Elenco prodotti'
        ],
        'toolbar'=> [],
        'columns' => [
            'sku',
            'marca.nome',
            'tipologia',
            'colore',
            'resa',
            'compatibile_prezzo',
            'enabled:boolean',
            [
              'class' => 'kartik\grid\ActionColumn',
              'template' => '{view} {update}',
              'urlCreator' => function($action, $model, $key, $index) {
                return Url::to(["toner-product/".$action,'id'=>$model["id"]]);
              },
              'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
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
