<?php

namespace app\controllers;
use yii\data\ArrayDataProvider;

use Yii;
use yii\console\Controller;

class GalileoController extends Controller {

  public function actionInvoiceWithoutPdf() {

    $sql = "select i.number, p.name, i.date_invoice, i.journal_id
  from account_invoice i
    join res_partner p on i.partner_id = p.id
    left outer join ir_attachment a on i.id = a.res_id and a.res_model = 'account.invoice'
  where i.state in ('open', 'paid')
    and a.res_id is null
    and i.journal_id not in (16, 20, 23, 24)";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $result,
      'sort' => false
    ]);

    return $this->render('report', [
        'dataProvider' => $dataProvider,
        'title' => "Fatture odoo senza pdf"
    ]);

  }

}
