<?php

namespace app\components\Odoo;

use app\components\OdooClient as Client;
use app\components\Util;

class Reconciliation {

  private $client;

  private $regex_account = [
    // Commissioni bancarie
    //"/(COMMISSIONI HOME BANKING)/" => m,
    //"/(RECUPERO IMPOSTA DI BOLLO)/" => 682,
    "/Ordinante: PAYPLUG Causale: ORDINE CONTO/i" => [
      "credit" => 726,
      "sign" => 1
    ],
    "/Ordinante: SDA EXPRESS COURIER S P A Causale: ORDINE CONTO/i" => [
      "credit" => 727,
      "sign" => 1
    ],
    "/Ordinante: PayPal .*Europe.* ORDINE CONTO/i" => [
      "credit" => 725,
      "sign" => 1
    ],
    // Giroconto Carta di credito
    "/UTILIZZO CARTA DI CREDITO.*CRED. CARTASI S.P.A./i" => [
      "debit" => 728,
      "sign" => -1
    ],
    "/UTILIZZO CARTA DI CREDITO.*CRED. NEXI S.P.A./i" => [
      "debit" => 728,
      "sign" => -1
    ],
    "/Pagamento automatico utenza -  - PayPal - /i" => [
      "debit" => 682,
      "sign" => -1
    ],
    "/Commissioni paypal/i" => [
      "debit" => 682,
      "sign" => -1
    ],
    "/Imposta di bollo/i" => [
      "debit" => 682,
      "sign" => -1
    ],
    /*"/DISPOSIZIONE DI BONIFICO.*CESARE PELLEGRINI/i" => [
      "debit" => 368,
      "sign" => -1
    ],
    "/DISPOSIZIONE DI BONIFICO.*ANDREA PIFFER/i" => [
      "debit" => 368,
      "sign" => -1
    ],*/
  ];

  function __construct() {

    $this->client = new Client();

  }

  /**
  Valorizzazione del partner sulle righe dell'estratto conto per i partner con pagamenti raggruppati
  **/
  public function reconciliePartner() {

    $partners = $this->client->search_read('res.partner',
      [
        ['comment', 'like', "###PAGAMENTO_RAGGRUPPATO###"],
      ],
      [
        "comment"
      ]);

    foreach ($partners as $partner) {

      $this->reconcilieSinglePartner($partner);

    }

  }

  private function reconcilieSinglePartner($partner) {

    $comments = explode("\n", $partner["comment"]);
    unset($comments[0]);

    foreach ($comments as $regex) {

      $line_ids = $this->client->search('account.bank.statement.line',
        [
          ['journal_entry_id', '=', False],
          ['partner_id', '=', False],
          ['name', 'ilike', $regex],
        ]);

      foreach ($line_ids as $line_id) {

        $this->setBankStatementLinePartner($line_id, $partner["id"]);

      }

    }

  }

  /**
  Riconciliazione delle fatture ancora non riconciliate
  **/
  public function reconcilieInvoice() {

    $invoices = $this->client->search_read('account.invoice',
      [
        ['reconciled', '=', False],
        //['id', '=', 311],
      ],
      [
        "number",
        "partner_id",
        "amount_total",
        "origin",
        "type",
        "residual",
        "journal_id",
      ], 1000);

    foreach ($invoices as $invoice) {
      $this->reconcilieSingleInvoice($invoice);
    };

  }

  private function reconcilieSingleInvoice($invoice) {

    /*if ($invoice["number"]=="AUE/2018/0010")
      print_r($invoice);*/

    if ($invoice["origin"]) {
      if ($this->processBankStatementLineLike($invoice, $invoice["origin"]))
        return;
    }

    // Verifica per partner id
    if ($this->processBankStatementPartnerId($invoice, $invoice["partner_id"][0]))
      return;

    // Recupero del partner
    $partner = $this->client->read("res.partner",
      $invoice["partner_id"][0],
      [
        "name",
        "firstname",
        "lastname",
        "email"
      ]);

    // Verifica per nome azienda
    if ($this->processBankStatementLineLike($invoice, $this->getPartnerName($partner["name"])))
      return;

    if ($partner["firstname"] && $partner["lastname"]) {
      if ($this->processBankStatementLineLike($invoice, $partner["firstname"]."%".$partner["lastname"]))
        return;
      if ($this->processBankStatementLineLike($invoice, $partner["lastname"]."%".$partner["firstname"]))
        return;
    }

  }

  private function getPartnerName($name) {

    $name = strtoupper($name);
    $name = str_replace(" S.N.C.", "", $name);
    $name = str_replace(" SNC", "", $name);
    $name = str_replace(" SRL", "", $name);
    $name = str_replace(" S.R.L.", "", $name);
    //echo "[$name]\n";
    return trim($name);

  }

  private function processBankStatementLineLike($invoice, $namelike) {

    $amount_total = $invoice["amount_total"];
    if ($invoice["type"]=="in_invoice")
      $amount_total = $amount_total * -1;

    $amount_start = $amount_total - 0.02;
    $amount_end = $amount_total + 0.02;

    $line_ids = $this->client->search('account.bank.statement.line',
      [
        ['journal_entry_id', '=', False],
        ['name', 'ilike', $namelike],
        ['amount', '>=', $amount_start],
        ['amount', '<=', $amount_end]
      ]);

    if (sizeof($line_ids)==1) {

      $this->move_line_invoice($line_ids[0], $invoice["id"]);
      return true;

    }

    return false;

  }

  private function processBankStatementPartnerId($invoice, $partner_id) {

    $amount_total = $invoice["amount_total"];

    if ($invoice["journal_id"][0]==22)
      $amount_total = $invoice["residual"];

    if ($invoice["type"]=="in_invoice")
      $amount_total = $amount_total * -1;

    $amount_start = $amount_total - 0.02;
    $amount_end = $amount_total + 0.02;

    $line_ids = $this->client->search('account.bank.statement.line',
      [
        ['journal_entry_id', '=', False],
        ['partner_id', 'ilike', $partner_id],
        ['amount', '>=', $amount_start],
        ['amount', '<=', $amount_end]
      ]);

    /*if ($invoice["number"] == "AUE/2018/0010") {
      print_r($invoice);
      print_r($line_ids);
      print "$amount_start $amount_end\n";
      print "------------------\n";
    }*/

    //return false;

    if (sizeof($line_ids)==1) {

      $this->move_line_invoice($line_ids[0], $invoice["id"]);
      return true;

    }

    return false;

  }

  /**
  Riconciliazione delle righe degli estratti conto bancari
  **/
  public function reconcilieBankStatementLine() {

    $lines = $this->client->search_read('account.bank.statement.line',
      [
        ['journal_entry_id', '=', False],
      ],
      [
        "ref",
        "name",
        "amount"
      ]);

    foreach ($lines as $line) {

      if ($line["ref"]!="") {
        if ($this->bankStatementLineByRef($line))
          continue;
      }

      if ($this->bankStatementLineWithAccount($line))
        continue;

    }

  }

  // Riconciliazione di una riga dell'estratto conto bancario con un conto (es. commissioni bancarie)
  private function bankStatementLineWithAccount($line) {

    foreach ($this->regex_account as $regex => $account) {

      $n = preg_match($regex, $line["name"], $matches, PREG_OFFSET_CAPTURE);

      if ($n==1) {
        $this->move_line_account($line["id"], $account);
        return true;
      }

    }

  }

  // Riconciliazione di una riga dell'estratto conto bancario by ref
  private function bankStatementLineByRef($line) {

    if ($line["ref"]=="")
      return false;

    $amount_start = $line["amount"] - 0.02;
    $amount_end = $line["amount"] + 0.02;

    /*if ($line["ref"]=="2K098597U65786600") {
      print "{$line["amount"]} $amount_start $amount_end\n";
    }*/

    $invoice = $this->client->search_read('account.invoice',
      [
        ['transaction_id', '=', $line["ref"]],
        ['reconciled', '=', False],
        ['amount_total', '>=', $amount_start],
        ['amount_total', '<=', $amount_end],
      ],
      [
        "partner_id",
        "move_id",
        "name",
        "number"
      ]);

    if (sizeof($invoice)==1) {
      $this->move_line_invoice($line["id"], $invoice[0]["id"]);
      return true;
    }

    return false;

  }

  // Collega una riga dell'estratto conto con un account
  private function move_line_account($line_id, $account) {

    $line = $this->client->read("account.bank.statement.line", $line_id, [
      "name",
      "amount"
    ]);

    $args = [
      0 => $line["id"],
      1 => [
        0 => [
          "account_id" => isset($account["credit"]) ? $account["credit"] : $account["debit"],
          "debit" => isset($account["debit"]) ? $line["amount"] * $account["sign"] : 0,
          "credit" => isset($account["credit"]) ? $line["amount"] * $account["sign"] : 0,
          "name" => $line["name"]
        ]
      ]
    ];

    $result = $this->client->execute('pointec.pointec', 'process_reconciliation', $args);

    echo "[account.bank.statement.line] conto collegato [{$args[1][0]["account_id"]}]\n";

    if (isset($result["faultCode"])) {
      Util::printError("Reconciliation.move_line_account", $result["faultString"]);
    }

  }

  // Collega una riga dell'estratto conto con la fattura
  private function move_line_invoice($line_id, $invoice_id) {

    /*if ($invoice_id!=404)
      return;*/

    $line = $this->client->read("account.bank.statement.line", $line_id, [
      "partner_id",
      "amount",
      "name"
    ]);

    $invoice = $this->client->read("account.invoice", $invoice_id, [
      "partner_id",
      "name",
      "number",
      "move_id",
      "supplier_invoice_number"
    ]);

    if (!is_array($line["partner_id"]))
      $this->setBankStatementLinePartner($line["id"], $invoice["partner_id"][0]);
    elseif($line["partner_id"][0] != $invoice["partner_id"][0])
      $this->setBankStatementLinePartner($line["id"], $invoice["partner_id"][0]);

    $name = "/";

    if ($invoice["name"]!="")
      $name = $invoice["name"];

    if ($invoice["supplier_invoice_number"]!="")
      $name = $invoice["supplier_invoice_number"];

    $move_lines = $this->client->search_read('account.move.line',
      [
        ['move_id', '=', $invoice["move_id"][0]],
        ['name', '=', $name],
      ],
      [
        "debit",
        "credit",
        "reconcile_partial_id",
        "amount_residual"
      ]);

    if (!isset($move_lines[0]))
      return;

    $move_line = $move_lines[0];

    /*if (isset($move_line["reconcile_partial_id"][0])) {
      $partial_reconciliation_siblings_ids = $this->client->search("account.move.line", [
        ['reconcile_partial_id', '=', $move_line["reconcile_partial_id"][0]],
        ["id", "!=", $move_line["id"]]
      ]);
      print_r($partial_reconciliation_siblings_ids);
    }*/

    //print_r($move_line);

    if ($move_line["debit"] > 0)
      $debit = (float) $move_line["amount_residual"];
    else
      $debit = 0;

    if ($move_line["credit"] > 0)
      $credit = (float) $move_line["amount_residual"];
    else
      $credit = 0;

    //$debit = (float) $move_line["debit"];
    //$credit = (float) $move_line["credit"];
    $amount = (float) $line["amount"];

    $args = [
      0 => $line["id"],
      1 => [
        0 => [
          "counterpart_move_line_id" => $move_line["id"],
          "credit" => $debit,
          "debit" => $credit,
          "name" => $invoice["number"]
        ]
      ]
    ];

    if ($debit > 0) {
      // Movimento dare per cui necessito di un incasso sul c/c
      $diff = round($debit - $amount, 2);

      if ($diff > 0) {
        // Ho un pagamento su una riga di estratto conto inferiore al totale della fattura
        // Registro un arrotondamento passivo
        $args[1][1] = [
            "account_id" => 658,
            "debit" => $diff,
            "name" => $line["name"]
        ];
      }

      if ($diff < 0) {

        echo "------ debit ------\n";
        print_r($args);
        print_r($move_line);
        echo "- $diff -----------------\n";
        return;

      }

    }

    if ($credit > 0) {

      $diff = round($credit + $amount, 2);

      if ($diff > 0) {
        echo "------ credit ------\n";
        print_r($args);
        print_r($move_line);
        echo "- [$credit] [$amount] [$diff] --\n";
        return;
      }

      if ($diff < 0) {
        echo "------ credit ------\n";
        print_r($args);
        print_r($move_line);
        echo "- [$credit] [$amount] [$diff] --\n";
        return;
      }

    }

    //echo "-----------------------\n";

    $result = $this->client->execute('pointec.pointec', 'process_reconciliation', $args);

    echo "[account.bank.statement.line] fattura collegata [{$invoice["number"]}]\n";

    if (isset($result["faultCode"])) {
      Util::printError("Reconciliation.move_line_invoice", $result["faultString"]);
    }

  }

  // Imposta il partner sulla riga dell'estratto conto
  private function setBankStatementLinePartner($line_id, $partner_id) {

    $data = [
      "partner_id" => $partner_id
    ];

    $result = $this->client->write('account.bank.statement.line', $line_id, $data);

    if (isset($result["faultCode"])) {
      Util::printError("Reconciliation.setBankStatementLinePartner", $result["faultString"]);
    }

    echo "[account.bank.statement.line] partner aggiornato [$partner_id]\n";

  }

}
