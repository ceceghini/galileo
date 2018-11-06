<?php

namespace app\components;

use Yii;

class Prestashop {

	private $apikey = [
    "stampaperfetta.it" => "BMIGM51WUDIY48WK4E1VHV74YHA4QL36",
		"tonerpertutti.it" => "AM139S3TMFJZHDNQ5LPJGFX3FCQ1YXCP"
  ];

  private $webService;

  public function __construct($shop) {

    $this->webService = new \PrestaShopWebservice("https://www.$shop", $this->apikey[$shop], true);

  }

  public function getProductInfo($reference) {

		/*$opt['resource'] = 'products';
		$opt['display']  = 'full';
		$opt['output_format'] = 'JSON';
		$opt['filter']['reference'] = "[$reference]";
		$xml = $this->webService->get($opt);*/

		$opt['resource'] = 'products';
		//$opt['display']  = 'full';
    //$opt['filter[reference]'] = $reference;
		$xml = $this->webService->get($opt);

		print_r($xml);

  }

}
