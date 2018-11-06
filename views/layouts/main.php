<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name." [".$_SERVER['PHP_AUTH_USER']."]",
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            [
              'label' => 'Toner',
              "items" => [
                ['label' => 'Marche', 'url' => ['/toner-marche/index']],
                ['label' => 'Serie', 'url' => ['/toner-serie/index']],
                ['label' => 'Modelli', 'url' => ['/toner-modelli/index']],
                ['label' => 'Prodotti', 'url' => ['/toner-product/index']],
                ['label' => 'Prodotti non collegati', 'url' => ['/toner-product/index-non-collegati']],
                ['label' => 'Prodotti da eliminare', 'url' => ['/toner-product/da-eliminare']],
              ]
            ],
            [
              'label' => 'Toner source',
              "items" => [
                ['label' => 'Marche', 'url' => ['/toner-source-marche/index']],
                ['label' => 'Serie', 'url' => ['/toner-source-serie/index']],
                ['label' => 'Modelli', 'url' => ['/toner-source-modelli/index']],
                ['label' => 'Prodotti', 'url' => ['/toner-source-product/index']],
              ]
            ],
            [
              'label' => 'Eurocali',
              "items" => [
                ['label' => 'Categorie', 'url' => ['/eurocali-category/index']],
                ['label' => 'Prodotti', 'url' => ['/eurocali-product/index']],
              ]
            ],
            [
              'label' => 'Report',
              "items" => [
//                ['label' => 'Vendite', 'url' => ['/product-sale/index']],
//                ['label' => 'Prezzi del venduto', 'url' => ['/toner-report/price-sale']],
//                ['label' => 'Prezzi verdestampa', 'url' => ['/toner-report/price-verdestampa']],
                ['label' => 'Prezzi OfferteCartucce VS', 'url' => ['/toner-report/prezzi-offertecartucce']],
                ['label' => 'Prezzi TuttoCartucce', 'url' => ['/toner-report/prezzi-tuttocartucce']],
                ['label' => 'Prezzi PuntoRigenera', 'url' => ['/toner-report/prezzi-puntorigenera']],
                ['label' => 'Prezzi Tonerper', 'url' => ['/toner-report/prezzi-tonerper']],
                ['label' => 'Prezzi Ecolors', 'url' => ['/toner-report/prezzi-ecolors']],
                '<li class="divider"></li>',
                ['label' => 'Prodotti compatibili più venduti 1Y', 'url' => ['/toner-report/sale-compatibile-1y']],
                '<li class="divider"></li>',
                ['label' => 'Verifica prodotti alta capacità', 'url' => ['/toner-report/prodotti-alta-capacita']],
                //['label' => 'Modelli con più prodotti', 'url' => ['/toner-report/modelli-con-piu-prodotti']],
                //['label' => 'Scostamento prezzi sorgente', 'url' => ['/toner-report/source-prezzo']],
                //['label' => 'Numero sorgenti eccessivo', 'url' => ['/toner-report/source-number']],
              ]
            ],
            [
              'label' => "Datastudio",
              "items" => [
                ['label' => 'Numero ordini', 'url' => 'https://datastudio.google.com/embed/reporting/1SCgM0Kr2Quj1z5e13TNoLeiyjcva1xqw/page/PZta', 'linkOptions' => ["target" => "_new"]],
                ['label' => 'Margine ordini', 'url' => 'https://datastudio.google.com/embed/reporting/127yfdx_WMZn5ESgnV2F6qyRr7Q78_6pn/page/PZta', 'linkOptions' => ["target" => "_new"]],
                ['label' => 'Tendenza ordini', 'url' => 'https://datastudio.google.com/embed/reporting/1JMfrJHK86BHiiF_E51fkypbbTT7mCVa7/page/PZta', 'linkOptions' => ["target" => "_new"]],
              ]
            ],
            [
              'label' => "Seo",
              'url' => ['/toner-seo/stampape-index?sort=-total']
            ],
            [
              'label' => 'Odoo',
              'items' => [
                ['label' => 'Fatture odoo senza pdf', 'url' => ['/odoo/invoice-without-pdf']],
                ['label' => 'Codici F24', 'url' => ['/odoo/f24']],
                ['label' => 'Fatture da inserire manualmente', 'url' => ['/odoo/invoice-manual']],
                ['label' => 'Verifica partner', 'url' => ['/odoo/partner-check']]
              ]
            ],
            ['label' => "Procedure",
              'items' => [
                ['label' => 'Ordini tagliato', 'url' => 'https://www.tagliato.it/feed/galileo/tagliato.php', 'linkOptions' => ["target" => "_new"]],
              ],
            ],
            ['label' => 'Comandi', 'url' => '/shell'],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container-fluid">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Pointec Srl <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
