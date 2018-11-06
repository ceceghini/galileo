<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\components\Array2Xml;

class ConservazioneController extends Controller {

  private $pdv;
  private $_parsedText;

  public function actionFatture() {

    $source = "/opt/files/conservazione/source/fatture vendita ecommerce";

    $this->initXml("2017");

    foreach (scandir($source) as $fileSource) {

      if ($fileSource != "." && $fileSource != "..") {

        $dest = "/opt/files/conservazione/2017/2017-FATTUREV-EC/$fileSource";

        copy("$source/$fileSource", $dest);

        $this->addFileToXml($source, $fileSource, $dest);

        //return;

      }

    }

    $this->saveXml("2017");

  }

  private function saveXml($year) {

  			$xml = Array2XML::createXML('PDV', $this->pdv);
  			//echo $xml->saveXML();
  			$dest = "/opt/files/conservazione/2017/2017-FATTUREV-EC";
  			$xml->save("$dest/IPDV-$year-FATTUREV-EC.xml");
  			//print_r($this->pdv);
  		}

  private function addFileToXml($source, $fileSource, $dest) {

  			//echo "$source, $fileSource";

        $this->_parsedText = \Spatie\PdfToText\Pdf::getText($source."/".$fileSource);

        $i = array();
        $i["DOCUMENTNO"] = str_replace(".pdf", "", $fileSource);

        //if ($i["DOCUMENTNO"]=="17-00068")
        //  print $this->_parsedText;

        $date = $this->parseValue(["Data\n\n(.*)\n\nN°"]);
        $i["UPDATED"] = substr($date, 6, 4)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
        $i["DATEINVOICED"] = $i["UPDATED"];

        $file = array();

  			$file["docid"] = $i["DOCUMENTNO"];
  			$file["filename"] = $fileSource;
  			$file["mimetype"] = "application/pdf";
  			$file["closingDate"] = $i["UPDATED"];
  			$hash = base64_encode(hash_file("sha256", $dest, true));
  			$file["hash"]["@attributes"]["algorithm"] = "SHA-256";
  			$file["hash"]["value"] = $hash;

  			$file["metadata"]["mandatory"]["singlemetadata"][] = $this->getSingleMetaData("conservazione.doc", "dataDocumentoTributario", $i["DATEINVOICED"]);
  			$file["metadata"]["mandatory"]["singlemetadata"][] = $this->getSingleMetaData("conservazione.doc", "oggettodocumento", "fattura");

  			$complexmetadata = array();
  			$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
  			$complexmetadata["@attributes"]["name"] = "soggettoproduttore";
  			$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
  			$complexmetadata["@attributes"]["nodeName"] = "soggetto";
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", "01975730225");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", "Pointec Srl");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", "01975730225");
  			$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

        $b = array();
        $b["NAME"] = $this->parseValue(["Spett. le\n(.*)\nTipo Documento"]);
        $b["FISCALCODE"] = $this->parseValue(["Banca d'Appoggio\n\n(.*)\n\n.*\n\n"]);
        $b["TAXID"] = $this->parseValue(["Banca d'Appoggio\n\n.*\n\n(.*)\n\n"]);

        if (sizeof($b["FISCALCODE"])!=16 && sizeof($b["FISCALCODE"])!=11)
          $b["FISCALCODE"] = '00000000000';

        if (sizeof($b["TAXID"])!=11)
          $b["TAXID"] = '00000000000';

        $complexmetadata = array();
  			$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
  			$complexmetadata["@attributes"]["name"] = "destinatario";
  			$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
  			$complexmetadata["@attributes"]["nodeName"] = "soggetto";
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", $b["FISCALCODE"]);
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", $b["NAME"]);
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", $b["TAXID"]);
  			$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

  			$complexmetadata = array();
  			$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
  			$complexmetadata["@attributes"]["name"] = "soggettotributario";
  			$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
  			$complexmetadata["@attributes"]["nodeName"] = "soggetto";
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", "01975730225");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", "Pointec Srl");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
  			$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", "01975730225");
  			$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

  			$this->pdv["files"]["file"][] = $file;

  		}

      private function getSingleMetaData($namespace, $name, $value) {

  			$singlemetadata = array();
  			$singlemetadata["namespace"] = $namespace;
  			$singlemetadata["name"] = $name;
  			$singlemetadata["value"] = $value;
  			return $singlemetadata;
  		}

      private function parseValue($regex) {

        foreach ($regex as $value) {

          if (preg_match("/$value/i", $this->_parsedText, $matcher)) {
            if ($matcher[1]!="")
              return $matcher[1];
          }

        }

      }

  private function initXml($year) {

		$this->pdv = array();
		$this->pdv["pdvid"] = "PDF-$year-FATTURE-EC";
		$this->pdv["docClass"] = array();
		$this->pdv["docClass"]["@value"] = "5045__Fatture";
		$this->pdv["docClass"]["@attributes"]["namespace"] = "conservazione.docExt";
		$this->pdv["files"]["file"] = array();

	}

  /*private $year = 2017;
  private $pdv;

  public function actionChiusura() {

    $year = $this->year;

    $this->initXmlchiusura($year);

    $source = "/opt/files/conservazione/$year/$year-CHIUSURA";

    foreach (scandir($source) as $fileSource) {

      if ($fileSource != "." && $fileSource != "..") {
        $this->addFileToXmlchiusura($source, $fileSource);
      }

    }

    $this->saveXmlchiusura($year);

  }

  private function initXmlchiusura($year) {

		$this->pdv = array();
		$this->pdv["pdvid"] = "PDF-$year-CHIUSURA";
		$this->pdv["docClass"] = array();
		$this->pdv["docClass"]["@value"] = "5993__Altri_documenti_contabili";
		$this->pdv["docClass"]["@attributes"]["namespace"] = "conservazione.docExt";
		$this->pdv["files"]["file"] = array();

	}

  private function saveXmlchiusura($year) {

    $dest = "/opt/files/conservazione/$year/$year-CHIUSURA";

    $xml = Array2XML::createXML('PDV', $this->pdv);
		$xml->save("$dest/IPDV-$year-CHIUSURA.xml");

	}

  private function addFileToXmlchiusura($dest, $nomeFileDest) {

		$file = array();
    $date = $this->year."-12-31";

		$file["docid"] = str_replace(".pdf", "", $nomeFileDest);
		$file["filename"] = $nomeFileDest;
		$file["mimetype"] = "application/pdf";
		$file["closingDate"] = $date;
		$hash = base64_encode(hash_file("sha256", "$dest/$nomeFileDest", true));
		$file["hash"]["@attributes"]["algorithm"] = "SHA-256";
		$file["hash"]["value"] = $hash;

		$file["metadata"]["mandatory"]["singlemetadata"][] = $this->getSingleMetaData("conservazione.doc", "dataDocumentoTributario", $date);
		$s = str_replace(".pdf", "", $nomeFileDest);
		$s = str_replace("-", " ", $nomeFileDest);
		$s = str_replace("  ", " ", $nomeFileDest);
		$file["metadata"]["mandatory"]["singlemetadata"][] = $this->getSingleMetaData("conservazione.doc", "oggettodocumento", $s);

		$complexmetadata = array();
		$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
		$complexmetadata["@attributes"]["name"] = "soggettoproduttore";
		$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
		$complexmetadata["@attributes"]["nodeName"] = "soggetto";
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", "01975730225");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", "Pointec Srl");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", "01975730225");
		$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

		$complexmetadata = array();
		$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
		$complexmetadata["@attributes"]["name"] = "destinatario";
		$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
		$complexmetadata["@attributes"]["nodeName"] = "soggetto";
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", "01975730225");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", "Pointec Srl");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", "01975730225");
		$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

		$complexmetadata = array();
		$complexmetadata["@attributes"]["namespace"] = "conservazione.doc";
		$complexmetadata["@attributes"]["name"] = "soggettotributario";
		$complexmetadata["@attributes"]["namespaceNode"] = "conservazione.soggetti";
		$complexmetadata["@attributes"]["nodeName"] = "soggetto";
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "codicefiscale", "01975730225");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "cognome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "denominazione", "Pointec Srl");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "nome", "");
		$complexmetadata["singlemetadata"][] = $this->getSingleMetaData("conservazione.soggetti", "partitaiva", "01975730225");
		$file["metadata"]["mandatory"]["complexmetadata"][] = $complexmetadata;

		$this->pdv["files"]["file"][] = $file;

	}

  private function getSingleMetaData($namespace, $name, $value) {

    $singlemetadata = array();
    $singlemetadata["namespace"] = $namespace;
    $singlemetadata["name"] = $name;
    $singlemetadata["value"] = $value;
    return $singlemetadata;
  }*/

}

?>
