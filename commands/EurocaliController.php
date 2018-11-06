<?php

namespace app\commands;

use yii\console\Controller;
use app\components\Proxy;
use app\models\Eurocali\Category;
use app\models\Eurocali\Product;
use app\models\Eurocali\ProductCategory;

class EurocaliController extends Controller
{

  private $url = "https://www.eurocali.it";
  private $source = "eurocali";
  private $proxy;
  private $main_category_url = [
    "https://www.eurocali.it/247-illuminazione-led",
    "https://www.eurocali.it/9-batterie-e-caricabatterie",
    "https://www.eurocali.it/293-pannelli-led"
  ];

  private $redirect = array(
    "https://www.eurocali.it/287-illuminazione" => "https://www.eurocali.it/247-illuminazione-led"
  );

  public function __construct($id, $module, $config = []) {
    $this->proxy = new \app\components\Proxy();
    parent::__construct($id, $module, $config = []);
  }

  public function actionProcessProduct() {

    $prodotti = Product::find()
      //->andwhere(["elaborato" => 0])
      ->andWhere(["id" => 26])
      ->limit(500)
      ->all();

    foreach ($prodotti as $prodotto) {
      $this->elaboraProdotto($prodotto);
    }

  }

  private function elaboraProdotto($prodotto) {

    //echo $prodotto->url."\n";

    if (!$prodotto->html) {
      echo "[$prodotto->url] caricamento html\n";
      $html = $this->getHtml($prodotto->url);
      $prodotto->html = $html;
      if (!$prodotto->save())
        print_r($prodotto->getErrors());
    }

    $ahtml = explode("\n", $prodotto->html);
    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($prodotto->html, []);
    $combination = preg_grep("/addCombination/", $ahtml);
    //print_r($combination);

    $varianti = array();
    foreach ($combination as $key => $value) {

      $variante = array();

      $tmp = explode(",", $value);
      //print_r($tmp);
      $variante["id"] = trim(str_replace("addCombination(", "", $tmp[0]));
      $variante["id_attribute"] = trim(str_replace("')", "", str_replace("new Array('", "", $tmp[1])));
      $variante["qty"] = trim($tmp[2]);
      $variante["price"] = trim($tmp[3]);
      $variante["ecotax"] = trim($tmp[4]);
      $variante["id_image"] = trim($tmp[5]);
      $variante["reference"] = trim($tmp[6]);

      //print_r($variante);

      if ($variante["id_image"] != -1) {
        $tmp = $dom->find("a[data-thumb-id={$variante["id_image"]}]");
        if (isset($tmp[0]))
          $variante["image"] = $tmp->href;
      }
      else {
        $tmp = $dom->find("a[id=MagicZoomPlusImageMainImage]");
        $variante["image"] = $tmp->href;
      }

      //print_r($variante);

      $tmp = $dom->find("div[id=attributes] select option[value={$variante["id_attribute"]}]");
      $variante["attribute"] = $tmp->innerHtml;

      if (isset($variante["image"])) {

        $dest = $this->downloadFile2($variante["image"], "product");

        $variante["local_image"] = $dest;

      }

      $varianti[] = $variante;
    }

    $tmp = $dom->find("div[id=short_description_content]");
    $prodotto->short_description = $this->htmlClean($tmp->innerHtml);

    $tmp = $dom->find("span[id=our_price_display]");
    $prodotto->price = str_replace(",", ".", str_replace(" €", "", $tmp->innerHtml));

    $tmp = $dom->find("h1[class=title_detailproduct] span");
    $prodotto->title = $tmp->innerHtml;

    $tmp = $dom->find("div[id=idTab1]");
    $description = $this->htmlClean($tmp->innerHtml);
    $data = $tmp->find("a");

    foreach ($data as $a) {

      $href = parse_url($a->href);

      if (!isset($href["domain"])) {

        $url = str_replace("../", "/", $a->href);

        if (strpos($url, "vimeo.com")!==false) {
          continue;
        }

        // Link a spedizioni
        if (strpos($url, "rivenditori-spedizione")!==false) {
          // Url di ricerca
          $q = "###SPEDIZIONI###";
          $description = str_replace($a->href, $q, $description);
          continue;
        }

        // Link con ricerca
        //if (substr($url, 0, 24)=="/cerca?controller=search") {
        if (strpos($url, "cerca?controller=search")!==false) {
          // Url di ricerca
          parse_str($href["query"], $tmp2);
          $q = "###SEARCH|{$tmp2["search_query"]}###";
          $description = str_replace($a->href, $q, $description);
          continue;
        }

        if (strpos($url, "cerca?controller=search")===false && strpos($url, "search_query")!=false) {
          $url = str_replace("../", "/", $href["path"]);
        }

        $url = str_replace("../", "/", $href["path"]);

        // Link al prodotto
        $products2 = Product::find()
          ->andWhere(["like", "url", $url])
          ->all();

        if (sizeof($products2)>1) {
          echo $prodotto->url."\n";
          echo "[href] più prodotti trovati [{$a->href}]\n";
          return;
        }

        if (sizeof($products2)==1) {
          $q = "###PID|{$products2[0]->id}###";
          $description = str_replace($a->href, $q, $description);
          continue;
        }

        // Link alla categorie
        $categories2 = Category::find()
          ->andWhere(["like", "url", $url])
          ->all();

        if (sizeof($categories2)>1) {
          echo $prodotto->url."\n";
          echo "[href] più categorie trovate [{$a->href}]\n";
          return;
        }

        if (sizeof($categories2)==1) {
          $q = "###CID|{$categories2[0]->id}###";
          $description = str_replace($a->href, $q, $description);
          continue;
        }

        $url3 = $this->url.$url;

        if (isset($this->redirect[$url3]))
          $url2 = $this->redirect[$url3];
        else {
          //print "$url3\n";
          $url2 = $this->proxy->redirectUrl($url3);
          //print "$url2\n";
          if ($url2 != $url3) {
            $this->redirect[$url3] = $url2;
          }
        }

        if ($url2 != $url3) {
          //echo "# ".$url2."\n";
          $products2 = Product::findOne(["url" => $url2]);
          if ($products2) {
            $q = "###PID|{$products2->id}###";
            $description = str_replace($a->href, $q, $description);
            continue;
          }

          $categories2 = Category::findOne(["url" => $url2]);
          if ($categories2) {
            $q = "###CID|{$categories2->id}###";
            $description = str_replace($a->href, $q, $description);
            continue;
          }
        }

        echo "[href] nessun link trovato [{$prodotto->url}] [$url]\n";
        print_r($href);

        return;

      }
      else {
        echo $prodotto->url."\n";
        echo "[href] link esterno [{$a->href}]\n";
        return;
      }
    }
    $data = $tmp->find("img");
    foreach ($data as $img) {
      if (strpos($img->src, "/photo/led/others")===false) {
          $dest = $this->downloadFile2($img->src, "others");
          $description = str_replace($img->src, $dest, $description);
      }
    }

    $prodotto->description = $description;

    $json_data = array();

    $tmp = $dom->find("ul[id=bestkit_icons_container] li img");
    foreach ($tmp as $value) {
      $json_data["bestkit"][] = $value->getAttribute("data-togle");
    }

    $json_data["varianti"] = $varianti;

    $prodotto->json_data = json_encode($json_data);

    $prodotto->elaborato = 1;

    if (!$prodotto->save())
      print_r($prodotto->getErrors());

  }

  private function processCategory($category, $p=null) {

    $url = $category["url"];
    if ($p)
      $url .= "?p=$p";

    $html = $this->getHtml($url);
    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $values = $dom->find("div[class=product-container]");

    foreach ($values as $div) {

      $a = $div->find("a[class=product-name]");
      $brand = $div->find("div[class=manufacturer_list] span");

      $product = Product::findOne(["url" => $a->href]);

      if (!$product) {
        $product = new Product();
        $product->url = $a->href;
        $product->brand = $brand->innerHtml;
        if (!$product->save())
          print_r($product->getErrors());
      }
      $product->present = 1;
      if (!$product->save())
        print_r($product->getErrors());

      $product_category = ProductCategory::findOne(["id_product" => $product->id, "id_category" => $category["id"]]);
      if (!$product_category) {
        $product_category = new ProductCategory();
        $product_category->id_product = $product->id;
        $product_category->id_category = $category["id"];
        if (!$product_category->save())
          print_r($product_category->getErrors());
      }

    }

    if ($p)
      return;

    $values = $dom->find("ul[class=pagination] li a");
    $n = sizeof($values)+1;
    for ($i=2;$i<$n;$i++) {
      $this->processCategory($category, $i);
    }

  }

  public function actionDownloadCategory() {

    $html = $this->getHtml($this->url);
    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $values = $dom->find("div[class=block_content] ul[class=tree]");
    $values = $values[0]->find("li");

    foreach ($values as $li) {

      $this->processCategoryLi($li);

    }

  }

  public function actionDownloadProduct() {

    // Caricamento prodotti
    $sql = "select a.id, a.url from eurocali_category a where and elaborato = 0 not exists (select 1 from eurocali_category b where b.id_parent = a.id)";
    $result = \Yii::$app->db->createCommand($sql)->queryAll();

    foreach ($result as $category) {

      $this->processCategory($category);

    }

  }

  private function processCategoryLi($li) {

    $a = $li->find("a");
    if (!in_array($a->href, $this->main_category_url))
      return;

    $title = $a->innerHtml;
    $url = $a->href;

    $category = Category::findOne(["url" => $url]);
    if (!$category) {
      $category = new Category();
      $category->url = $url;
    }
    $category->title = $this->htmlClean(trim($title));
    $category->present = true;

    if (!$category->save())
      print_r($category->getErrors());

    // Elaborazione sottocategorie
    $html = $this->getHtml($url);
    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $value = $dom->find("div[class=cat_desc]");

    $description = strip_tags($value->innerHtml, "<strong> <br> <p>");
    $description = str_replace("<p></p>", "", $description);
    $category->description = $this->htmlClean($description);
    if (!$category->save())
      print_r($category->getErrors());

    $values = $dom->find("div[class=subcategories]");
    foreach ($values as $div) {
      $this->processCategoryDiv($div, $category);
    }

  }

  private function processCategoryDiv($div, $parent) {

    $a = $div->find("a[class=img]");
    $img = $div->find("div[class=img_subcate] a img");

    $h3 = $div->find("h3");
    $title = $h3->innerHtml;
    $url = $a->href;

    $category = Category::findOne(["url" => $url]);
    if (!$category) {
      $category = new Category();
      $category->url = $url;
    }

    $category->title = $this->htmlClean(trim($title));
    $category->present = true;
    $category->id_parent = $parent->id;
    if (!$category->img) {
      if (sizeof($img)> 0)
        $category->img = $this->downloadFile2($img->src, "category");
    }


    if (!$category->save()) {
      echo $url."\n";
      print_r($category->getErrors());
    }

    // Elaborazione sottocategorie
    //echo $url."\n";
    $html = $this->getHtml($url);
    $dom = new \PHPHtmlParser\Dom;
    $dom->loadStr($html, []);

    $value = $dom->find("div[class=cat_desc]");

    if (isset($value[0])) {
      $description = strip_tags($value->innerHtml, "<strong> <br> <p>");
      $description = str_replace("<p></p>", "", $description);
      $category->description = $this->htmlClean($description);
      if (!$category->save())
        print_r($category->getErrors());
    }

    $values = $dom->find("div[class=subcategories]");
    foreach ($values as $div) {
      $this->processCategoryDiv($div, $category);
    }

    //$category->elaborato = 1;
    if (!$category->save())
      print_r($category->getErrors());

  }

  public function getHtml($url) {

		return $this->proxy->curl($url);

	}

  private function downloadFile2($source, $dir) {

    $info = pathinfo($source);
    $dest = "/photo/led/$dir/".$info["basename"];

    $this->downloadFile($source, $dest);

    return $dest;

  }

  private function downloadFile($source, $dest) {

    if (!file_exists("/opt/galileo$dest")) {
      $this->proxy->downloadFile($source, "/opt/galileo$dest");
      //echo "[$source] => [$dest]\n";
    }

  }

  private function htmlClean($string) {

    $clean = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    $clean = str_replace("&nbsp;", "", $clean);
    $clean = str_replace("&nbsp", "", $clean);
    return trim($clean);

  }

  public function actionTest() {

    $s = Product::find()->all();
    foreach ($s as $value) {
      $value->description = str_replace("/photo/eurocali/", "/photo/led/", $value->description);
      $value->save();
    }

  }

}
