<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;

class ShellController extends Controller {

  public function actions()
	{
		return [
			'odoo-import-bank-crgiovo'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-bank-crgiovo',
        'label' => "Import estratto conto cassa rurale"
			],
      'odoo-import-bank-payplug'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-bank-payplug',
        'label' => "Import estratto conto PAYPLUG"
			],
      'odoo-import-bank-paypal'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-bank-paypal',
        'label' => "Import estratto conto PAYPAL"
			],
      'odoo-import-bank-cartasi'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-bank-cartasi',
        'label' => "Import estratto conto CARTASI"
			],
      'odoo-import-bank-sda'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-bank-sda',
        'label' => "Import estratto conto SDA"
			],
      /*'odoo-import-account-invoice'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/import-account-invoice',
        'label' => 'Importa fatture di acquisto'
			],*/
      'odoo-reconcilie'=>[
				'class'=>'app\components\ShellAction',
				'command'=>'php /opt/galileo/yii odoo/reconcilie',
        'label' => 'Riconcilia pagamenti'
			],
		];
	}

  public function actionIndex($command = null) {

    $menuitems = array();

    $menuitems = [
      ["label" => "Import estratti conto", "items" => [
        ["label" => "Cassa rurale", "url" => "/shell?command=odoo-import-bank-crgiovo"],
        ["label" => "Payplug", "url" => "/shell?command=odoo-import-bank-payplug"],
        ["label" => "Paypal", "url" => "/shell?command=odoo-import-bank-paypal"],
        ["label" => "Cartasi", "url" => "/shell?command=odoo-import-bank-cartasi"],
        ["label" => "Sda", "url" => "/shell?command=odoo-import-bank-sda"]
        ]],
      ["label" => "Riconcilia", "url" => "/shell?command=odoo-reconcilie"]
    ];

    /*$dataProvider = new ArrayDataProvider([
      'allModels' => $data,
      'sort' => false
    ]);*/

    if ($command)
      $title = $this->actions()[$command]["label"];
    else
      $title = "";

    return $this->render('index', [
        'menuitems' => $menuitems,
        'command' => $command,
        'title' => $title
    ]);

  }

}
