<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;
use app\components\Util;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use jlorente\remainingcharacters\RemainingCharacters;

/* @var $this yii\web\View */
/* @var $model app\models\TonerProduct */

$this->title = $model->sku." [$model->colore]";
if ($model->marca)
  $this->title .= " [".$model->marca->nome."]";
$this->params['breadcrumbs'][] = ['label' => 'Toner Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
  <div class="toner-product-view">

    <h1 class="col-sm-12"><?= Html::encode($this->title) ?></h1>

    <div class="col-xs-5">

      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="glyphicon glyphicon-list"></i> Dati prodotto
          </h3>
          <div class="clearfix"></div>
        </div>

        <div class="panel-body">

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

          <div class="form-group">
              <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
          </div>

          <?php ActiveForm::end(); ?>

        </div>
      </div>

      <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'sku',
            [
              'attribute' => "id_marca",
              'value' => isset($model->marca) ? $model->marca->nome : "MARCA MANCANTE",
              'label' => "Marca"
            ],
            'tipologia',
            'colore',
            'resa',
            'compatibile:boolean',
            'part_number'
        ],
      ]) ?>

      <?= GridView::widget([
          'dataProvider' => $dataProvider1,
          /*'panel' => [
            'type' => 'info',
            'heading' => '<i class="glyphicon glyphicon-euro"></i> Keyword'
          ],*/
          'toolbar'=> [],
          'summary' => '',
          'columns' => [
            'keyword',
            'click',
            'impression',
          ],
      ]); ?>

    </div>

    <div class="col-xs-7">

      <div id="ajaxCrudDatatable">

      <?= GridView::widget([
          'id'=>'crud-datatable-prodotti',
          'dataProvider' => $dataProvider2,
          'pjax'=>true,
          'panel' => [
            'type' => 'primary',
            'heading' => '<i class="glyphicon glyphicon-euro"></i> Modelli collegati'
          ],
          'toolbar'=> [],
          'summary' => '',
          'columns' => [
            'marca',
            'serie',
            'nome',
            'impression',
            'stampapeseostate',
            [
              'class' => 'kartik\grid\ActionColumn',
              'template' => '{update}',
              'headerOptions' => ['style' => 'width:80px'],
              'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
              'urlCreator' => function ($action, $model, $key, $index) {
                return Url::to(["stampape-update", "id" => $model["id"]]);
              },
            ],
          ],
      ]); ?>

      </div>

  </div>

</div>

<?php Modal::begin([
  "id"=>"ajaxCrudModal",
  'size'=>'modal-lg',
  "footer"=>"",// always need it for jquery plugin
  "options" => ['tabindex' => false,],
])?>
<?php Modal::end(); ?>
