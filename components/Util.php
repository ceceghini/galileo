<?php

namespace app\components;

use Yii;

class Util {

  private static $debug = false;

  public static function getFormatBoolean() {
    return [0=>"No", 1=>"Sì"];
  }

  public static function curlJson($source) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec ($ch);
    curl_close ($ch);

    return json_decode($data, true);

  }

  public static function printError($title, $result) {

    $text = json_encode($result);

    $message = \app\models\Message::findOne([
      "title" => $title,
      "text" => $text
    ]);

    $now = date("Y-m-d h");

    if ($message)
      if ($now <= $message->date)
        return;

    echo "---------------------------------------------------\n";
    echo $title."\n";
    echo "---------------------------------------------------\n";
    print_r($result);
    print "\n";
    echo "---------------------------------------------------\n\n";

    \app\models\Message::add($title, "WARNING", $text, null);

  }

  public static function downloadFile($url, $destination) {

    $ch = curl_init();
    //curl_setopt( $this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );
		//curl_setopt( $this->ch, CURLOPT_PROXY, $torSocks5Proxy );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HEADER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt( $ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true);
		curl_setopt( $ch, CURLOPT_URL, $url );
		$data = curl_exec( $ch );

		$file = fopen($destination, "w+");
		fputs($file, $data);
		fclose($file);
    curl_close ($ch);

	}

  public static function getSource() {
    return [
      "tuttocartucce" => "tuttocartucce",
      "puntorigenera" => "puntorigenera",
      "supplies24" => "supplies24",
    ];
  }

  public static function getTipologiaSale() {
    return [
      "COMPATIBILE" => "COMPATIBILE",
      "ORIGINALE" => "ORIGINALE",
    ];
  }

  public static function getPeriodSale() {
    return [
      "3Y" => "3Y",
      "1Y" => "1Y",
    ];
  }

  public static function getPrezzoOriginaleFromSupplies($price) {

    return round(round($price * 1.3 * 1.22, 1) / 1.22, 1);

  }

  public static function getCostoPuntorigenera($price, $tax) {

    if ($tax)
      return round($price / 1.22 * 0.96, 1);
    else
      return round($price * 0.96, 1);

  }

  public static function getPrezzoCompatibileFromPuntorigenera($costo, $_p) {

    if ($_p->tipologia=="CARTUCCIA") {
      if ($costo < 1)
        return $costo * 4;
      else
        return $costo * 2;
    }
    else {
      if ($costo < 10)
        return $costo * 2.5;
      elseif ($costo < 35)
        return $costo * 2;
      elseif ($costo < 55)
        return $costo * 1.5;
      else
        return $costo * 1.1;
    }

  }

  public static function getImporto($s) {

    $str = str_replace("€", "", $s);
    $str = trim($str);

    if (strpos($str, ".") === false && strpos($str, ",") === false) {
      return (float) $str;
    }

    if (substr($str, strlen($str)-3, 1)==",") {
      return (float) str_replace(",", ".", str_replace(".", "", $str));
    }

    if (substr($str, strlen($str)-2, 1)==",") {
      return (float) str_replace(",", ".", str_replace(".", "", $str));
    }

    return (float) $str;

	}

  public function reducePdf($f) {

    Util::debug($f->getFileName());

    $source = $f->getRealPath();
    if (substr($f->getFileName(), 0, 2)=="__")
      $dest = $f->getPath()."/".$f->getFileName();
    else
      $dest = $f->getPath()."/__".$f->getFileName();

    if (file_exists($dest)) {
      chmod($dest, 0777);
      //unlink($source);
      return $dest;
    }

    //$cmd = "gs -q -dBATCH -dNOPAUSE -dNOOUTERSAVE -dUseCIEColor -sProcessColorModel=DeviceCMYK -sDEVICE=pdfwrite -sPDFACompatibilityPolicy=1 -dPDFSETTINGS=/ebook -sOutputFile=\"$dest\" \"$source\" > /dev/null";
    $cmd = "gs -q -dBATCH -dNOPAUSE -dNOOUTERSAVE -dUseCIEColor -sProcessColorModel=DeviceCMYK -sDEVICE=pdfwrite -sPDFACompatibilityPolicy=1 -dPDFSETTINGS=/ebook -sOutputFile=\"$dest\" \"$source\" > /dev/null";
    //echo $cmd."\n";

    $response = shell_exec($cmd);

    echo "File ridimensionato[$dest]\n";

    if (file_exists($dest)) {
      chmod($dest, 0777);
      unlink($source);
    }

    return $dest;

  }

  public static function setDebug() {
    self::$debug = true;
  }

  public static function debug($str) {

    if (self::$debug)
      print $str."\n";

  }

}

 ?>
