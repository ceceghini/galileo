<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Toner\Marche;

/* @var $this yii\web\View */
/* @var $searchModel app\models\Toner\SerieSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Serie';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="serie-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'nome',
            'id_verdestampa:boolean',
            [
              'attribute' => 'id_marca',
              'value' => 'marca.nome',
              'filter' => Html::activeDropDownList($searchModel, "id_marca", yii\helpers\ArrayHelper::map(Marche::find()->asArray()->all(), 'id', 'nome'), ["class" => 'form-control', 'prompt' => '...'])
            ],
        ],
    ]); ?>
</div>
