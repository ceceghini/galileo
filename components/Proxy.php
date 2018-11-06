<?php

namespace app\components;

use Yii;

class Proxy {

	private $ch, $proxy;

	function __construct() {

		$torSocks5Proxy = "socks5://127.0.0.1:9050";
		//$torSocks5Proxy = "http://93.51.247.104:80";

		$this->ch = curl_init();

		/*curl_setopt( $this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );
		curl_setopt( $this->ch, CURLOPT_PROXY, $torSocks5Proxy );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_HEADER, false );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		//curl_setopt( $this->ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
		curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);*/

		curl_setopt( $this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5 );
		curl_setopt( $this->ch, CURLOPT_PROXY, $torSocks5Proxy );
		curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_HEADER, false );

	}

	public function setopt($type, $headers) {

		curl_setopt($this->ch, $type, $headers);

	}

	public function curl($url) {

		curl_setopt($this->ch, CURLOPT_URL, $url);

		$data = curl_exec( $this->ch );

		echo curl_error($this->ch);

		return $data;

	}

	/*public function curlRefillJson($url, $request_body) {

		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
		if ($request_body)
      curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request_body);
		if ($request_body)
      curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($request_body))
      );

		curl_setopt( $this->ch, CURLOPT_URL, $url );
		$data = curl_exec( $this->ch );
		return curl_exec( $this->ch );

	}*/

	/*public function curl( $url, $postParameter = null, $headers = null ) {

		if( sizeof( $postParameter ) > 0 )
			curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $postParameter );

		if( sizeof( $headers ) > 0 )
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

		curl_setopt( $this->ch, CURLOPT_URL, $url );
		return curl_exec( $this->ch );

	}*/

	public function downloadFile($url, $destination) {

		if (file_exists($destination))
			return;

		curl_setopt( $this->ch, CURLOPT_URL, $url );
		$data = curl_exec( $this->ch );

		$file = fopen($destination, "w+");
		fputs($file, $data);
		fclose($file);

	}

	public function redirect($url) {

		curl_setopt($this->ch, CURLOPT_HEADER, true);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $this->ch, CURLOPT_URL, $url );
		$data = curl_exec($this->ch);
		
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

	}

	public static function redirectUrl($url) {

		$proxy = new Proxy();
		return $proxy->redirect($url);

	}

	function __destruct() {

		curl_close( $this->ch );

	}

}
