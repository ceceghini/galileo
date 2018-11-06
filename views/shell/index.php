<?php

use kartik\grid\GridView;
use kartik\sidenav\SideNav;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Comandi';
$this->params['breadcrumbs'][] = 'Galileo - Pointec srl';
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-2">

              <?= SideNav::widget([
                  'type' => SideNav::TYPE_PRIMARY,
                  'heading' => "Comandi disponibili",
                  'items' => $menuitems
              ]); ?>

            </div>
            <div class="col-lg-8">

                <?php if ($command): ?>

                  <div class="panel panel-primary">
                    <div class="panel-heading">
                      <div class="pull-right">
                        <?= \yii\bootstrap\Button::widget([
                        	'label'=>'Esegui',
                        	'options'=>[
                        		'data-shell-widget-run'=>'my-shell-widget',
                            'class' => "btn btn-success"
                        	],
                        ]) ?>
                      </div>
                      <h3 class="panel-title">
                        <i class="glyphicon glyphicon-book"></i>  <?=$title ?>
                      </h3>
                    </div>
                    <div class="panel-body">

                      <?= \app\widgets\ShellWidget::widget([
                      	'id'=>'my-shell-widget',
                      	'route'=>['shell/'.$command],
                      	'autorun'=>false,
                      	'initialContent'=>Yii::t('app', 'Ready and waiting...'),
                      	'clientOptions'=>[
                              //custom client options here
                      	],
                      ]) ?>

                    </div>
                  </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
