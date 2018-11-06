<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\widgets\ActiveForm;
use jlorente\remainingcharacters\RemainingCharacters;

/* @var $this yii\web\View */
/* @var $model app\models\TonerProduct */

?>
<div class="toner-product-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php

    $dataProvider = new ArrayDataProvider([
      'allModels' => $model->getKeywords(),
      'pagination' => [
        'pageSize' => 100
      ]
    ]);

    $columns = array_keys($dataProvider->allModels[0]);

    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
        'showPageSummary' => false,
        'panel' => [
    		  'type'=>GridView::TYPE_DEFAULT,
          'footer' => false,
          'heading' => "Keywords"
        ],
        'showFooter' => false,
        'toolbar' => [
      		'content' => "",
          	'',
          	//'{toggleData}'
      	],
    ]); ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'stampape_title')->widget(RemainingCharacters::classname(), [
        'type' => RemainingCharacters::INPUT_TEXT,
        'text' => Yii::t('app', '{n} characters remaining'),
        'label' => [
            'tag' => 'p',
            'id' => 'my-counter',
            'class' => 'counter',
            'invalidClass' => 'error'
        ],
        'options' => [
            'class' => 'col-md-12',
            'maxlength' => 80,
        ]
    ]); ?>

    <?= $form->field($model, 'stampape_description')->widget(RemainingCharacters::classname(), [
        'type' => RemainingCharacters::INPUT_TEXTAREA,
        'text' => Yii::t('app', '{n} characters remaining'),
        'label' => [
            'tag' => 'p',
            'id' => 'my-counter',
            'class' => 'counter',
            'invalidClass' => 'error'
        ],
        'options' => [
            'class' => 'col-md-12',
            'rows' => 5,
            'maxlength' => 3000,
        ]
    ]); ?>

    <?= $form->field($model, 'stampape_metatitle')->widget(RemainingCharacters::classname(), [
        'type' => RemainingCharacters::INPUT_TEXT,
        'text' => Yii::t('app', '{n} characters remaining'),
        'label' => [
            'tag' => 'p',
            'id' => 'my-counter',
            'class' => 'counter',
            'invalidClass' => 'error'
        ],
        'options' => [
            'class' => 'col-md-12',
            'maxlength' => 80,
        ]
    ]); ?>

    <?= $form->field($model, 'stampape_metadescription')->widget(RemainingCharacters::classname(), [
        'type' => RemainingCharacters::INPUT_TEXTAREA,
        'text' => Yii::t('app', '{n} characters remaining'),
        'label' => [
            'tag' => 'p',
            'id' => 'my-counter',
            'class' => 'counter',
            'invalidClass' => 'error'
        ],
        'options' => [
            'class' => 'col-md-12',
            'maxlength' => 300,
        ]
    ]); ?>

    <?php ActiveForm::end(); ?>

</div>
