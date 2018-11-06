<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\Util;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\ProductSaleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-sale-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
    ]); ?>
</div>
