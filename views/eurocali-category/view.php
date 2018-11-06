<?php

use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Eurocali\Category */
?>
<div class="category-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'url:url',
            'description:html',
            'present',
            'elaborato',
            'img',
            'id_parent',
        ],
    ]) ?>

</div>
