<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TonerProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = "SEO stampaperfetta.it";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="toner-product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php

    $columns = array_keys($dataProvider->models[0]->attributes);
    $columns[] = [
      'class' => 'kartik\grid\ActionColumn',
      'template' => '{update}',
      'urlCreator' => function ($action, $model, $key, $index) {
        return Url::to(["toner-product/update", "id" => $model["id"]]);
      },
    ];

    ?>

  <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
          "sku",
          "marca",
          "tipologia",
          "colore",
          "qty",
          "total",
          "stampapeseostate" => [
            "format"=> "html",
            "value" => function ($model, $key, $index, $column) {

              $modelli = \app\models\Toner\Modelli::find()
                ->joinWith("products")
                ->andWhere(["id_prodotto" => $model["id"]])
                ->all();

              $n = 0;
              $i = 0;
              foreach ($modelli as $value) {
                $n ++;
                if ($value->getStampapeSeoState()=="4/4")
                  $i++;
              }

              if ($i==$n)
                return "<b>$i/$n</b>";
              else
                return "$i/$n";
            },
          ],
          [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{stampape-view}',
            'buttons' => [
              "stampape-view" => function ($url, $model, $key) {
                return Html::a('modifica',$url."?id=".$model["id"], ['data-pjax' => 0, 'target' => "_blank"]);
              },
            ],
            'headerOptions' => ['style' => 'width:80px'],
          ],
        ],
        'showPageSummary' => false,
        'panel' => [
    		  'type'=>GridView::TYPE_DEFAULT,
        ],
        'toolbar' => [
      		'content' => "",
          	'',
          	//'{toggleData}'
      	],
    ]); ?>

</div>
