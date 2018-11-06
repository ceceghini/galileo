<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\TonerProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="toner-product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'enabled')->checkBox() ?>

    <?= $form->field($model, 'manuale')->checkBox() ?>

    <?= $form->field($model, 'secondario')->checkBox() ?>

    <?= $form->field($model, 'id_marca')->dropdownList(yii\helpers\ArrayHelper::map(\app\models\Toner\Marche::find()->asArray()->all(), 'id', 'nome')) ?>

    <?= $form->field($model, 'tipologia')->dropdownList(\app\models\Toner\Product::getTipologie()) ?>

    <?= $form->field($model, 'colore')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'resa')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'compatibile_prezzo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'compatibile_url_foto')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'originale_url_foto')->textInput(['maxlength' => true]) ?>

    <?php ActiveForm::end(); ?>

</div>
