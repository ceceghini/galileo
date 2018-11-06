<?php

namespace app\controllers;
use yii\data\ArrayDataProvider;

use Yii;
use yii\console\Controller;

class OdooController extends Controller {

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

  public function actionPartnerCheck() {

    $sql = 'select distinct p.name, p.firstname, p.lastname, p.vat, p.fiscalcode, p.individual
  from account_invoice i
    join res_partner p on i.partner_id = p.id
 where journal_id in (2, 3, 4, 15, 5)
   and (
   	(p.firstname is null and char_length(p.fiscalcode) = 16)
   )';

   $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

   $dataProvider = new ArrayDataProvider([
     'allModels' => $result,
     'sort' => false
   ]);

   return $this->render('report', [
       'dataProvider' => $dataProvider,
       'title' => "Verifica Partner"
   ]);

  }

  public function actionF24() {

    $sql = "select distinct l.name as codice, a.code as codice_conto, a.name as conto
  from account_move m
    join account_move_line l on l.move_id = m.id
    join account_account a on l.account_id = a.id
 where m.ref like '%f24%'
   order by 1";

    $result = \Yii::$app->dbOdoo->createCommand($sql)->queryAll();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $result,
      'sort' => false
    ]);

    return $this->render('report', [
        'dataProvider' => $dataProvider,
        'title' => "Elenco codici F24"
    ]);

  }

  public function actionInvoiceManual() {

    $invoice = new \app\components\Odoo\AccountInvoice();
    $files = $invoice->getInvoiceManual();

    $dataProvider = new ArrayDataProvider([
      'allModels' => $files,
      'sort' => false
    ]);

    return $this->render('report', [
        'dataProvider' => $dataProvider,
        'title' => "Fatture da inserire manualmente"
    ]);

  }

}
