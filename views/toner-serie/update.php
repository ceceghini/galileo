<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Serie */

$this->title = 'Update Serie: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Series', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="serie-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
