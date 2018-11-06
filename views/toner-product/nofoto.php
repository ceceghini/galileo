<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\Toner\Product;
use app\components\Util;
use app\models\Toner\Marche;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TonerProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Prodotti senza foto';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="toner-product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php

    $columns = array_keys($dataProvider->allModels[0]);
    $columns[] = [
      'class' => 'kartik\grid\ActionColumn',
      'template' => '{view}',
      'urlCreator' => function ($action, $model, $key, $index) use ($url) {
        if (!$url)
          return;
        return Url::to(["$url/$action", "id" => $model["id"]]);
      },
    ];

    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
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
