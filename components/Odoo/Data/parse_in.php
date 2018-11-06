<?php

return [

  //////////////////////////////////////////////////////////////////////////////
  "dicon" => [
    "supplier_invoice_number" => [
      "Data\n(.*)\n.*\n% Sconti"
    ],
    "date_invoice" => [
      "Data\n.*\n(.*)\n% Sconti"
    ],
    "amount_total" => [
      "Totale Documento\n((?!Segue).*)\nAcconto"
    ],
    "date_format" => "d/m/Y",
    "res_partner_id" => 7219,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "gruppo_one" => [
    "supplier_invoice_number" => [
      "Numero\nFATTURA\n(.*)\nCondizione pagamento",
      "Numero\nFATTURA\nData\n(.*)\nCondizione pagamento"
    ],
    "date_invoice" => [
      "Cliente\n(.*)\nTelefono cliente"
    ],
    "amount_total" => [
      "Totale fattura \( S. E. & O. \)\n(.*)\nmittente"
    ],
    "date_format" => "d/m/y",
    "res_partner_id" => 9522,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "serverplan" => [
    "supplier_invoice_number" => [
      "Fattura n. (.*) del .*\nCOD"
    ],
    "date_invoice" => [
      "Fattura n. .* del (.*)\nCOD"
    ],
    "amount_total" => [
      "TOTALE FATTURA\n(.*) Euro\nVi preghiamo"
    ],
    "date_format" => "d/m/Y",
    "res_partner_id" => 9046,
    "account_id" => 469,
    "name" => "Hosting / Domini web",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "supplies24" => [
    "supplier_invoice_number" => [
      "\n.*\n(.*)\nK3594570\nIT01975730225"
    ],
    "date_invoice" => [
      "\n(.*)\n.*\nK3594570\nIT01975730225"
    ],
    "amount_total" => [
      "totale:\n(.*)\nLa ringraziamo per il Suo ordine",
      "totale:\n(.*)\n.* pagato con",
      "Importo totale:\n(.*) .*"
    ],
    "date_format" => "d/m/Y",
    "exclude" => "accredito",
    "res_partner_id" => 7279,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "intra" => true,
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "puntorigenera" => [
    "supplier_invoice_number" => [
      "Pag.\n.*\n.*\n(.*)\nBanca d'appoggio",
      "Data doc.\n.*\n.*\n(.*)\nCodice e descr",
      "Data doc.\n(.*)\nPag.\n.*\n.*\nBanca d'appoggio"
    ],
    "date_invoice" => [
      "Pag.\n.*\n(.*)\n.*\nBanca d'appoggio",
      "Data doc.\n.*\n(.*)\n.*\nCodice e descr",
      "Data doc.\n.*\nPag.\n.*\n(.*)\nBanca d'appoggio"
    ],
    "amount_total" => [
      "TOTALE DOCUMENTO\n.*\n(.*)\nScadenze",
      "TOTALE DOCUMENTO\n.*\n((?!Spett).*)",
    ],
    "date_format" => "d/m/Y",
    "exclude" => "NOTA CREDITO",
    "res_partner_id" => 7274,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "samo" => [
    "supplier_invoice_number" => [
      "Fattura nr. (.*)\nData: .*\n"
    ],
    "date_invoice" => [
      "Fattura nr. .*\nData: (.*)\n"
    ],
    "amount_total" => [
      "Totale dovuto\n(.*)"
    ],
    "date_format" => "d/m/Y",
    "exclude" => "NOTA CREDITO",
    "res_partner_id" => 7626,
    "account_id" => 520,
    "name" => "Consulenza seo",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "ecoservice" => [
    "supplier_invoice_number" => [
      "Data doc.\/ Date\n01975730225\n(.*)\n.*\nTelefono",
      "Data doc.\/ Date\n01975730225\n(.*)\n.*\nCodice fiscale"
    ],
    "date_invoice" => [
      "Data doc.\/ Date\n01975730225\n.*\n(.*)\nTelefono",
      "Data doc.\/ Date\n01975730225\n.*\n(.*)\nCodice fiscale"
    ],
    "amount_total" => [
      "TOTALE DOCUMENTO [\/] TOTAL INVOICE\n([\d]+[\,][\d]+)\n"
    ],
    "date_format" => "d/m/Y",
    "exclude" => "Nota Credito",
    "res_partner_id" => 7276,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "ngi" =>
  [
    /*"regex_poreference" =>	"FATTURA\n\n(.*)\n\n(.*)\n\n01975730225",
    "group_poreference" => 2,
    "regex_date" => null,
    "group_date" => 1,
    "regex_total" => "Aliquota Iva\n22 %\n(.*)\n\nEOLO",
    "group_total" => 1,
    "date_format" =>	"d/m/Y",
    "exclude" => null,
    "sku" => "INT",
    "c_bpartner_id" => 1000762*/
    "supplier_invoice_number" => [
      "DATI FATTURA\nNumero (.*)\nDel .*\nCODICE",
      "FATTURA\n.*\nNumero fattura (.*)\nDel .*\nCODICE",
      "FATTURA\n.*\n(.*)\n01975730225"
    ],
    "date_invoice" => [
      "DATI FATTURA\nNumero .*\nDel (.*)\nCODICE",
      "FATTURA\n.*\nNumero fattura .*\nDel (.*)\nCODICE",
      "FATTURA\n(.*)\n.*\n01975730225"
    ],
    "amount_total" => [
      "TOTALE FATTURA\n(.*) euro\n.\nEOLO",
      "Totale Fattura\n(.*)\nAliquota Iva"
    ],
    "date_format" =>	"d/m/Y",
    "exclude" => "",
    "res_partner_id" => 8153,
    "account_id" => 536,
    "name" => "Internet",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "ovh" => [
    "supplier_invoice_number" => [
      "Fattura quietanzata: (.*)\nData:\n.*\n"
    ],
    "date_invoice" => [
      "Fattura quietanzata: .*\nData:\n(.*)\n"
    ],
    "amount_total" => [
      "TOTALE IVA INCLUSA\n.*\n.*\n(.*)\nOvh",
      "TOTALE IVA INCLUSA\n(.*)\nOvh",
      "TOTALE IVA INCLUSA\n.*\n(.*)\nOvh",
      ".*\n.*\n(.*)\nOvh S"
    ],
    "date_format" =>	"d-m-Y",
    "res_partner_id" => 7218,
    "account_id" => 469,
    "name" => "Hosting / Domini web",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "aruba" =>
  [
    "supplier_invoice_number" => [
      "Numero Doc.\n.*\n(.*)\nPag."
    ],
    "date_invoice" => [
      "Numero Doc.\n(.*)\n.*\nPag."
    ],
    "amount_total" => [
      "Totale\n.*\n.*\n.*\n.*\n.*\n(.*)"
    ],
    "date_format" =>	"d/m/Y",
    "res_partner_id" => 8048,
    "account_id" => 469,
    "name" => "Hosting / Domini web",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "sda" =>
  [
    "supplier_invoice_number" => [
      "FATTURA Nº\n(.*)\nDATA\nPAGAMENTO\n.*\n30"
    ],
    "date_invoice" => [
      "FATTURA Nº\n.*\nDATA\nPAGAMENTO\n(.*)\n30"
    ],
    "amount_total" => [
      "IMPORTO TOTALE\n(.*)\nIn caso"
    ],
    "date_format" =>	"d-mY",
    "res_partner_id" => 7273,
    "account_id" => 479,
    "name" => "Corriere espresso",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "verdestampa" =>
  [
    "supplier_invoice_number" => [
      "Via dei Boscati, 28\n(.*)\n.*\n60\n38030"
    ],
    "date_invoice" => [
      "Via dei Boscati, 28\n.*\n(.*)\n60\n38030"
    ],
    "amount_total" => [
      "\n(.*)\nPagina"
    ],
    "date_format" =>	"d/m/Y",
    "res_partner_id" => 8151,
    "account_id" => 488,
    "name" => "Comparatori prezzi",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  /*"ovh_intra" => [
    "supplier_invoice_number" => [
      "Fattura quietanzata: (.*)\nData:\n.*\n"
    ],
    "date_invoice" => [
      "Fattura quietanzata: .*\nData:\n(.*)\n"
    ],
    "amount_total" => [
      "TOTALE IVA INCLUSA\n.*\n.*\n(.*)\nOvh",
      "TOTALE IVA INCLUSA\n(.*)\nOvh",
      "TOTALE IVA INCLUSA\n.*\n(.*)\nOvh"
    ],
    "date_format" =>	"d-m-Y",
    "res_partner_id" => 7218,
    "product_id" => 2324
  ],*/
  //////////////////////////////////////////////////////////////////////////////
  "settepixel" =>
  [
    "supplier_invoice_number" => [
      //"Descrizione\n\n(.*)\n\nPrezzo" => 1,
      "N°\n.*\n(.*)\nPrezzo",
      "Descrizione\n.*\n(.*)\nPrezzo\nImporto"
    ],
    "date_invoice" => [
      "N°\n(.*)\n.*\nPrezzo",
      "Descrizione\n(.*)\n.*\nPrezzo\nImporto"
    ],
    "amount_total" => [
      "Totale da pag. al netto dello sconto €\n(.*)\nCod.IVA",
    ],
    "date_format" =>	"d-m-Y",
    "res_partner_id" => 7236,
    "account_id" => 488,
    "name" => "Comparatori prezzi",
    "insert" => true
  ],
  //////////////////////////////////////////////////////////////////////////////
  "life365" =>
  [
    "supplier_invoice_number" => [
      "Fattura accompagnatoria\n(.*)\n.*\n"
    ],
    "date_invoice" => [
      "Fattura accompagnatoria\n.*\n(.*)\n"
    ],
    "amount_total" => [
      "CONDIZIONI DI PAGAMENTO\nTotale documento\n(.*)\n",
    ],
    "date_format" =>	"d/m/Y",
    "exclude" => "Nota di credito",
    "res_partner_id" => 7237,
    "account_id" => 464,
    "name" => "Toner e cartucce per rivendita",
    "insert" => true
  ],

];
