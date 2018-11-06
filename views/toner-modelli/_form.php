<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\TonerModelli */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="toner-modelli-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nome')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'serie')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marca')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tipologia')->dropDownList([""=>"", "TONER" => "TONER", "CARTUCCE" => "CARTUCCE", "TTR" => "TTR"]) ?>

    <?= $form->field($model, 'photo')->textInput(['maxlength' => true]) ?>

    <?php ActiveForm::end(); ?>

</div>
