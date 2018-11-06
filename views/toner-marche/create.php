<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Toner\Marche */

$this->title = 'Create Marche';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="marche-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
