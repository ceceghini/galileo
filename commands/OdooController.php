<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use \app\components\Odoo;
use app\components\OdooClient as Client;

class OdooController extends Controller {

  public function actionImportOrderTest() {

    $import = new Odoo\ImportOrder();
    $import->importtest();

  }

  public function actionImportOrder() {

    /*$connection = @fsockopen("127.0.0.1", 5069);
    if (!is_resource($connection))
      return;
    fclose($connection);*/

    $import = new Odoo\ImportOrder();
    $import->import();
    $import->setOrigin();

  }

  /*public function actionSetPurchasePrice() {

    $import = new Odoo\ImportOrder();
    $import->setPurchasePrice();

  }*/

  public function actionProcessSale() {

    $sale = new Odoo\Sale();
    $sale->import();

  }

  public function actionImportAccountInvoice() {

    //\app\components\Util::setDebug();
    $import = new Odoo\AccountInvoice();
    $import->importIn();
    $import->validateIn();

  }

  public function actionImportAccountInvoiceTest() {

    $import = new Odoo\AccountInvoice();
    $import->importIn(true);

  }

  public function actionReduceAccountInvoice() {

    \app\components\Util::setDebug();
    $import = new Odoo\AccountInvoice();
    $import->reducePdf();

  }

  public function actionInvoiceFromOrder() {

    $import = new Odoo\ImportOrder();
    $import->invoiceFromOrder();

    $invoice = new Odoo\AccountInvoice();
    $invoice->validateOut();
    $invoice->sendInvoice();

  }

  public function actionReconcilie() {

    $reconciliation = new Odoo\Reconciliation();
    $reconciliation->reconcilieBankStatementLine();
    $reconciliation->reconcilieInvoice();
    $reconciliation->reconciliePartner();

  }

  /*public function actionImportBank() {

    $bank = new Odoo\AccountBank\CrGiovo();
    $bank->import();

    $bank = new Odoo\AccountBank\Payplug();
    $bank->import();

    $bank = new Odoo\AccountBank\Paypal();
    $bank->import();

    $bank = new Odoo\AccountBank\CartaSi();
    $bank->import();

    $bank = new Odoo\AccountBank\Sda();
    $bank->import();

  }*/

  public function actionImportBankCrgiovo() {

    $bank = new Odoo\AccountBank\CrGiovo();
    $bank->import();

  }

  public function actionImportBankCartasi() {

    $bank = new Odoo\AccountBank\CartaSi();
    $bank->import();

  }

  public function actionImportBankSda() {

    $bank = new Odoo\AccountBank\Sda();
    $bank->import();

  }

  public function actionImportBankPayplug() {

    $bank = new Odoo\AccountBank\Payplug();
    $bank->import();

  }

  public function actionImportBankPaypal() {

    $bank = new Odoo\AccountBank\Paypal();
    $bank->import();

  }

  public function actionNormalize() {

    $attachment = new Odoo\Other();

    $attachment->setAttachmentPartentId();

    $attachment->deleteNotification();

  }

  public function actionParsePdf() {

    $file = "/opt/files/fatture/puntorigenera/__43153.pdf";

    $_parsedText = \Spatie\PdfToText\Pdf::getText($file);
    $_parsedText = str_replace("\n\n", "\n", $_parsedText);

    print $_parsedText."\n\n";

    preg_match("/TOTALE DOCUMENTO\n.*\n((?!Spett).*)/i", $_parsedText, $matcher);

    //if (sizeof($matcher[1]==1) && $matcher[1][0]!="")
    //print $matcher[1][0];

    //preg_match("/Totale Documento\n(.*)\nAcconto\n/i", $_parsedText, $matcher);

    print_r($matcher);

  }

  public function actionReopenInvoice() {

    $invoice = new Odoo\AccountInvoice();
    $invoice->reopenInvoice();

  }

  public function actionPrepareIntra() {

    $invoice = new Odoo\AccountInvoice();
    $invoice->prepareIntra();

  }

  public function actionCheckPartner() {

    $import = new Odoo\ImportOrder();
    $import->checkPartnerVat();

  }

  public function actionTest() {

    $sql = "select id, price_unit from sale_order_line";
    $result = \Yii::$app->dbOdooTest->createCommand($sql)->queryAll();
    foreach ($result as $value) {

      \Yii::$app->dbOdoo->createCommand()->update(
        "sale_order_line",
        ["price_unit" => $value["price_unit"]],
        "id = {$value["id"]}"
        )->execute();

    }

  }

}

 ?>
