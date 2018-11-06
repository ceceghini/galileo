<?php

namespace app\components\Odoo;

use app\components\OdooClient as Client;
use app\components\Util;

class Other {

  private $parent = [
    15 => 16,  // Fatture EC
    2  => 12,  // Fatture clienti
    4  => 12,  // Note credito clienti
    24 => 14,  // Note credito PA
    22 => 13,  // Fatture fornitori intra
    25 => 15,  // Fatture fornitori autofatture
    3  => 10,  // Fatture fornitori
    5  => 10,  // Note di credito fornitori
    16 => 17,   // Corrispettivi
  ];

  function __construct() {

    $this->client = new Client();

  }

  public function setAttachmentPartentId() {

    $attachments = $this->client->search_read("ir.attachment", [
      ["parent_id", "=", False],
      ["res_model", "=", "account.invoice"],
//      ["id", "=", 1030]
    ], [
      "id",
      "res_name",
      "res_id",
      "name"
    ], 1000);

    foreach ($attachments as $attachment) {

      $invoice = $this->client->read("account.invoice", $attachment["res_id"], ["journal_id", "period_id", "number", "state"]);

      if ($invoice["state"]=="draft")
        continue;

      if (!isset ($this->parent[$invoice["journal_id"][0]])) {
        echo "Attachment journal_id non previsto [{$invoice["journal_id"][0]}] [{$attachment["res_name"]}]\n";
        continue;
      }

      $parent_id = $this->parent[$invoice["journal_id"][0]];

      if (substr($attachment["name"], 0, 3)=="INV")
        $name = $attachment["name"];
      else
        $name = $invoice["period_id"][1]."--".str_replace("/", "-", $invoice["number"]).".pdf";

      $data = [
        "parent_id" => $parent_id,
        "name" => $name
      ];

      //print_r($data);
      //print $attachment["id"];

      $result = $this->client->write("ir.attachment", $attachment["id"], $data);

      if (isset($result["faultCode"])) {
        Util::printError("Other.setAttachmentPartentId", $result["faultString"]." [{$attachment["id"]}] [$name] [{$attachment["name"]}]");
      }

    }

  }

  public function deleteNotification() {

    $ids = $this->client->search('mail.message', [], 0, 1000);
    $this->client->unlink('mail.message', $ids);

    /*$ids = $client->search('mail.notification', [], 0, 100);
    $client->unlink('mail.notification', $ids);*/

    $ids = $this->client->search('mail.followers', [], 0, 1000);
    $this->client->unlink('mail.followers', $ids);

  }

}
