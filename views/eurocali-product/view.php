<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Eurocali\Product */
?>
<div class="product-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'url:url',
            'brand',
            'present',
            'elaborato',
            'short_description:html',
            'price',
            'title',
            'description:html',
            //'html:ntext',
            //'json_data',
        ],
    ]) ?>

</div>
