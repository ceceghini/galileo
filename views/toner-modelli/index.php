<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\TonerModelli;
use yii\helpers\Url;
use mickgeek\actionbar\Widget as ActionBar;
use app\components\Util;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TonerModelliSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Modelli stampanti';
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
?>
<div class="toner-modelli-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $massive = Html::a('Nuovo modello', ["create"], ['class' => 'btn btn-success', 'role'=>'modal-remote']); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true,
        'id' => 'modelli-view',
        'columns' => [
            'nome',
            [
              'attribute' => 'enabled',
              'format' => 'boolean',
              'filter' => Html::activeDropDownList($searchModel, "enabled", Util::getFormatBoolean(), ["class" => 'form-control', 'prompt' => '...'])
            ],
            /*[
              'attribute' => 'NumberProduct'
            ],*/
            'tipologia',
            'serie',
            'marca',
            'id_verdestampa:boolean',
            [
              'attribute' => "photo",
              'format' => ['image',['width'=>'50','height'=>'50']]
            ],
            [
              'class' => 'kartik\grid\ActionColumn',
              'template' => '{view} {update} {delete}',
              'headerOptions' => ['style' => 'width:80px'],
              'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
            ],
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

    <?php Modal::begin([
      "id"=>"ajaxCrudModal",
      "footer"=>"",// always need it for jquery plugin
      "options" => ['tabindex' => false,],
    ])?>
    <?php Modal::end(); ?>

    <?php

  	    $this->registerJs("
  	    	$('#massive-update-modelli').on('click', function() {
  	    		var keys = $('#modelli-view').yiiGridView('getSelectedRows');
  	    		if (!keys.length) {
  	    			alert('Seleziona almeno un modello');
  	    			return;
  	    		}
            else {
  	    			window.location.href='" . Url::to(['toner-modelli/massive-update']) . "?ids=' + keys.join() + '&destination=' + encodeURIComponent(window.location.href);
  	    		}
  	    	});

  	    ");

      ?>

</div>
