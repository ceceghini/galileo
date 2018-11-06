<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Toner\Source\Marche;
use app\models\Toner\Source\Serie;
use app\models\Toner\Source\Modelli;
use app\models\Toner\Source\Product;
use app\models\Toner\Source\ProductModelli;
use app\models\Toner\Source\Url;

abstract class SourceController extends Controller {

  abstract protected function processMain();
  abstract protected function processBrandSingle($_marca);
  abstract protected function processSerieSingle($_serie);
  abstract protected function processModelliSingle($_modello);
  abstract protected function processProductSingle($_product);
  abstract protected function processUrlSingle($_url);
  abstract protected function disableProduct();

  public function actionProcessMain() {
    $this->processMain();
  }

  public function actionResetWeekly() {

    \Yii::$app->db->createCommand("update toner_source_serie set is_present = 0 where source = '{$this->source}'")->query();
    \Yii::$app->db->createCommand("update toner_source_modelli set is_present = 0, elaborato = 0 where source = '{$this->source}'")->query();
    \Yii::$app->db->createCommand("update toner_source_url set is_present = 0, elaborato = 0 where source = '{$this->source}'")->query();
    \Yii::$app->db->createCommand("update toner_source_product set elaborato = 0, is_present = 0 where source = '{$this->source}'")->query();

  }

  protected function deleteUnusedProduct() {

    $sql = "delete from toner_source_product
where source = '$this->source'
 and is_present = 0
 and not exists (select 1 from toner_source_product_join j where toner_source_product.id = j.id_source_product and j.disabled <> 1)";

  \Yii::$app->db->createCommand($sql)->query();

  }

  public function actionProcessProduct() {

    $_products = Product::find()
      ->andWhere([
        "source" => $this->source,
        "elaborato" => 0
      ])
      ->all();

    foreach ($_products as $_product) {

      $this->processProductSingle($_product);

    }

    $this->deleteUnusedProduct();

    $this->disableProduct();

  }

  public function actionJoinModelli() {

    $_modelli = Modelli::find()
      ->andWhere([
        "source" => $this->source,
        "id_modello" => null
      ])
      ->all();

    foreach ($_modelli as $_modello) {

      if (!$_modello->sourceSerie->id_serie)
        continue;

      $model = \app\models\Toner\Modelli::find()
        ->andWhere([
          "nome" => $_modello->nome,
          "id_serie" => $_modello->sourceSerie->id_serie
        ])
        ->all();

      if (sizeof($model)>1)
        echo "JOIN MODELLI - Più elementri tovati [{$_modello->nome}] [{$_modello->sourceSerie->id_serie}]\n";

      if (sizeof($model)==1) {
        $_modello->id_modello = $model[0]->id;
        $_modello->save();
        echo "Modello collegato [{$_modello->sourceSerie->nome}] [$_modello->nome]\n";
      }

    }

  }

  public function actionJoinSerie() {

    $_series = Serie::find()
      ->andWhere([
        "source" => $this->source,
        "id_serie" => null
      ])
      ->all();

    foreach ($_series as $_serie) {

      $model = \app\models\Toner\Serie::find()
        ->andWhere([
          "nome" => $_serie->nome,
          "id_marca" => $_serie->sourceMarca->id_marca
        ])
        ->all();

      if (sizeof($model)>1)
        echo "JOIN SERIE - Più elementri tovati [{$_serie->nome}] [{$_serie->sourceMarca->id_marca}]\n";

      if (sizeof($model)==1) {
        $_serie->id_serie = $model[0]->id;
        $_serie->save();
        echo "Serie collegata [{$_serie->source}] [{$_serie->sourceMarca->nome}] [$_serie->nome]\n";
      }

    }

  }

  // Collegamento delle marche source con le marche effettive
  private function joinBrand() {

    $_marche = Marche::find()
      ->andWhere([
        "source" => $this->source,
        "id_marca" => null
      ])
      ->all();

    foreach ($_marche as $_marca) {

      if (isset($this->marche_alias[$_marca->nome]))
        $nome_marca = $this->marche_alias[$_marca->nome];
      else
        $nome_marca = $_marca->nome;

      $model = \app\models\Toner\Marche::findOne(["nome" => $nome_marca]);

      if ($model) {
        $_marca->id_marca = $model->id;
        $_marca->save();
        echo "Marca collegata [{$_marca->nome}]\n";
      }

    }

  }

  // Elaborazione marche
  public function actionProcessBrand() {

    $_marche = Marche::find()
      ->andWhere(["source" => $this->source])
      ->andWhere(["is not", "id_marca", null])
      ->all();

    foreach ($_marche as $_marca) {

      $this->processBrandSingle($_marca);

    }

  }

  // Elaborazione serie
  public function actionProcessSerie() {

    $_series = Serie::find()
      ->andWhere(["source" => $this->source, "is_present" => 1])
      ->all();

    foreach ($_series as $_serie) {

      $this->processSerieSingle($_serie);

    }

  }

  // Elaborazione modelli
  public function actionProcessModelli () {

    $_modelli = Modelli::find()
      ->andWhere([
        "source" => $this->source,
        "elaborato" => 0,
        "is_present" => 1
      ])
      ->limit(2000)
      ->all();

    foreach ($_modelli as $_modello) {

      if ($this->processModelliSingle($_modello)) {
        $_modello->elaborato = 1;
        $_modello->save();
      }

    }

  }

  // Elaborazione modelli
  public function actionProcessUrl () {

    $_urls = Url::find()
      ->andWhere([
        "source" => $this->source,
        "elaborato" => 0,
        "is_present" => 1
      ])
      ->limit(2000)
      ->all();

    foreach ($_urls as $_url) {

      if ($this->processUrlSingle($_url)) {
        $_url->elaborato = 1;
        $_url->save();
      }

    }

  }

  // Elaboraizione della singola marca
  protected function elaboraMarca($marca, $key, $exists = false) {

    $marca = trim(strtoupper($marca));

    if ($exists) {
      if (isset($this->marche_alias[$marca]))
        $marca2 = $this->marche_alias[$marca];
      else
        $marca2 = $marca;

      $model = \app\models\Toner\Marche::findOne(["nome" => $marca2]);
      if (!$model)
        return;
    }

    $model = Marche::findOne(["nome" => $marca, "source" => $this->source]);

    if (!$model) {
      $model = new Marche();
      $model->nome = $marca;
      $model->source = $this->source;
      $model->source_key = trim($key);

      if ($model->save())
        echo "source_marca salvata [$marca]\n";
      else {
        echo "source_marca ERRORE [$marca]\n";
        print_r($model->getErrors());
      }
    }

	}

  // Elaborazione serie singola
  protected function elaboraSerie($_marca, $serie, $key) {

    $serie = trim(strtoupper($serie));

    $model = Serie::findOne(["nome" => $serie, "id_source_marca" => $_marca->id, "source" => $this->source]);

    if (!$model) {
      $model = new Serie();
      $model->source = $this->source;
      $model->nome = $serie;
      $model->id_source_marca = $_marca->id;
      $model->source_key = trim($key);

      if ($model->save())
        echo "source_serie salvata [$_marca->nome] [$serie]\n";
      else {
        echo "source_serie ERRORE [$_marca->nome] [$serie]\n";
        print_r($model->getErrors());
      }
    }
    else {
      $model->source_key = trim($key);
    }

    $model->is_present = 1;
    if(!$model->save()) {
      print_r($model->getErrors());
    }

  }

  // Elaborazione modello singola
  protected function elaboraModello($_marca, $_serie, $modello, $key, $url) {

    $modello = str_replace(" ", "", trim(strtoupper($modello)));

    $model = Modelli::findOne(["nome" => $modello, "id_source_serie" => $_serie->id, "source" => $this->source]);

    if (!$model) {
      $model = new Modelli();
      $model->source = $this->source;
      $model->id_source_serie = $_serie->id;
      $model->serie = $_serie->nome;
      $model->marca = $_marca->nome;
      $model->nome = $modello;
      $model->source_key = trim($key);
      $model->url = $url;

      if ($model->save())
        echo "source_modello salvato [$_marca->nome] [$_serie->nome] [$modello]\n";
      else {
        echo "source_modello ERRORE [$_marca->nome] [$_serie->nome] [$modello]\n";
        print_r($model->getErrors());
      }
    }
    else {
      $model->url = $url;
      $model->source_key = trim($key);
    }

    $model->is_present = 1;
    if(!$model->save()) {
      print_r($model->getErrors());
    }

  }

  // Elaborazione modello singola
  protected function elaboraProdotto($url, $key, $_modello = null) {

    $key = trim($key);

    //$model = Product::findOne(["url" => $url, "source" => $this->source]);
    $model = Product::findOne(["source_key" => $key, "source" => $this->source]);

    if (!$model) {
      $model = new Product();
      $model->source = $this->source;
      $model->url = $url;
      $model->source_key = $key;

      if ($model->save())
        echo "source_product salvato [$model->url]\n";
      else {
        echo "source_product ERRORE [$model->url]\n";
        print_r($model->getErrors());
      }
    }

    $model->url = $url;
    $model->is_present = 1;
    $model->save();

    if (!$_modello)
      return;

    $pm = ProductModelli::findOne(["id_product" => $model->id, "id_modello" => $_modello->id]);
    if (!$pm) {
      $pm = new ProductModelli();
      $pm->id_product = $model->id;
      $pm->id_modello = $_modello->id;
      $pm->save();
    }


  }

  // Elaborazione serie singola
  protected function elaboraUrl($url) {

    $model = Url::findOne(["url" => $url, "source" => $this->source]);

    if (!$model) {
      $model = new Url();
      $model->url = $url;
      $model->source = $this->source;

      if ($model->save())
        echo "source_url salvato [$url]\n";
      else {
        echo "source_url ERRORE [$url]\n";
        print_r($model->getErrors());
      }
    }

    $model->is_present = 1;
    $model->save();

  }

  // Aggiornamento prodotto
  protected function updateProduct($_product) {

    $_product->elaborato = 1;
    if (!$_product->save()) {
      echo "source_product ERRORE [$_product->source_key]\n";
      print_r($_product->getErrors());
    }

  }

}
