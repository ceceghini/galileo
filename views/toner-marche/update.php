<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Marche */

$this->title = "Update Marche: $model->nome";
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="marche-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
