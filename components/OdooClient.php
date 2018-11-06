<?php

namespace app\components;

class OdooClient extends \OdooClient\Client {

  public function __construct($test = false) {

    if ($test) {
      $this->host = \Yii::$app->params['odooTest']['host'];
      $this->user = \Yii::$app->params['odooTest']['user'];
      $this->password = \Yii::$app->params['odooTest']['password'];
      $this->database = \Yii::$app->params['odooTest']['database'];
    }
    else {
      $this->host = \Yii::$app->params['odoo']['host'];
      $this->user = \Yii::$app->params['odoo']['user'];
      $this->password = \Yii::$app->params['odoo']['password'];
      $this->database = \Yii::$app->params['odoo']['database'];
    }

    /*\Yii::$app->db



    if ($test)
      $this->database = "test";*/
  }

  public function searchOne($model, $criteria) {

    $ids = $this->search($model, $criteria, 0, 1);
    if (sizeof($ids)==1)
      return $ids[0];
    else
      return null;

  }

  public function execute($model, $action, $args) {

    $response = $this->getClient('object')->execute_kw(
        $this->database,
        $this->uid(),
        $this->password,
        $model,
        $action,
        $args
      );

    return $response;

  }

  public function test() {

    $response = $this->getClient('object')->execute_kw(
        $this->database,
        $this->uid(),
        $this->password,
        'account.bank.statement.line',
        'get_move_lines_for_reconciliation_by_statement_line_id',
        [7]
//        [
//          'uid' => [16],
//        ]
    );

    print_r($response);

  }

  /*public function signal_workflow($model, $ids, $signal)
	{
        $response = $this->getClient('object')->execute_kw(
            $this->database,
            $this->uid(),
            $this->password,
            $model,
            'signal_workflow',
            [],
            [
              'ids' => $ids,
              'signal'=>'invoice_open'
            ]
        );

		return $response;
	}*/

}
