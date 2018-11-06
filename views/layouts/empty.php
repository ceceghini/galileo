<?php
/*unset($this->assetBundles['yii\bootstrap\BootstrapAsset']);
unset($this->assetBundles['yii\web\JqueryAsset']);
unset($this->assetBundles['yii\web\YiiAsset']);
unset($this->assetBundles['kartik\widgets\Select2Asset']);
unset($this->assetBundles['kartik\widgets\WidgetAsset']);
unset($this->assetBundles['kartik\dialog\DialogYiiAsset']);
unset($this->assetBundles['kartik\grid\GridExportAsset']);
unset($this->assetBundles['kartik\grid\GridResizeColumnsAsset']);
unset($this->assetBundles['kartik\grid\GridViewAsset']);
unset($this->assetBundles['kartik\dialog\DialogBootstrapAsset']);
unset($this->assetBundles['kartik\dialog\DialogBootstrapAsset']);
unset($this->assetBundles['yii\bootstrap\BootstrapPluginAsset']);*/

$this->assetBundles = [];

?>
<?php $this->beginPage() ?>
<?php $this->head() ?>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
<?php $this->endPage() ?>
