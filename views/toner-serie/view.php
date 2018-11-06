<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Toner\Serie */

$this->title = "{$model->marca->nome} {$model->nome}";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="serie-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nome',
            'marca.nome',
        ],
    ]) ?>



</div>
