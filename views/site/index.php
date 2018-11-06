<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Galileo - Pointec srl';
$this->params['breadcrumbs'][] = 'Galileo - Pointec srl';
?>
<div class="site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <?= GridView::widget([
                    'dataProvider' => $dataProviderVS,
                    'panel' => [
                      'type' => 'primary',
                      'heading' => 'Elaborazioni VERDESTAMPA'
                    ],
                    'toolbar'=> [],
                    'summary' => '',
                    'showFooter' => false,
                    'columns' => [
                        'TITLE',
                        'N',
                        'ALERT'
                    ],
                ]); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProviderMessage,
                    'panel' => [
                      'type' => 'primary',
                      'heading' => 'Messaggi'
                    ],
                    'toolbar'=> [],
                    'summary' => '',
                    'showFooter' => false,
                    'columns' => [
                        'level',
                        [
                          'attribute' => "text",
                          'format' => 'html',
                          'value' => function ($model, $key, $index, $column) {
                            return Html::a($model->text, Url::to($model->url));
                          }
                        ],
                        [
                          'class' => 'yii\grid\ActionColumn',
                          'template' => '{delete}',
                          'urlCreator' => function ($action, $model, $key, $index) {
                            return Url::to(["message/$action", "id" => $model->id]);
                          }
                        ],
                    ],
                ]); ?>
            </div>
            <div class="col-lg-2">

              <table class="table table-sm">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col">Conto</th>
                    <th scope="col">Descrizione</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">731140</th>
                    <td>Spese bancarie</td>
                  </tr>
                  <tr>
                    <th scope="row">625100</th>
                    <td>Energia elettrica</td>
                  </tr>
                  <tr>
                    <th scope="row">625120</th>
                    <td>Riscaldamento</td>
                  </tr>
                  <tr>
                    <th scope="row">260420</th>
                    <td>Debiti v/clienti c/anticipi</td>
                  </tr>
                </tbody>
                </table>

            </div>

            <div class="col-lg-4">

              

            </div>
        </div>

    </div>
</div>
