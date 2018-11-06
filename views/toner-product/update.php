<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TonerProduct */

?>
<div class="toner-product-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
