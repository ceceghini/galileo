<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TonerModelli */

$this->title = 'Modifica modello: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Toner Modellis', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="toner-modelli-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
