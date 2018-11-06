<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_modelli".
 *
 * @property integer $id
 * @property string $nome
 * @property string $serie
 * @property string $marca
 * @property integer $id_serie
 *
 * @property TonerSerie $idSerie
 */
class Modelli extends \yii\db\ActiveRecord
{

    public $readonly = array();

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_modelli';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome', 'serie', 'marca'], 'required'],
            //[['serie'], 'readOnly'=>true],
            [['id_serie', "enabled", "id_verdestampa"], 'integer'],
            [['nome', 'serie', 'marca', 'photo', 'tipologia'], 'string', 'max' => 255],
            [['stampape_title'], 'string', 'max' => 150],
            [['stampape_metatitle'], 'string', 'max' => 80],
            [['stampape_description'], 'string', 'max' => 3000],
            [['stampape_metadescription'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Modello',
            'serie' => 'Serie',
            'marca' => 'Marca',
            'id_serie' => 'Id Serie',
        ];
    }

    // Restituisce se il prodtto è collegato ad un modello
    public function isJoined($id) {

      $join = \app\models\Toner\ProductModelli::findOne(["id_modello" => $this->id, "id_prodotto" => $id]);

      if ($join)
        return $join->disabled;
      else
        return null;

    }

    public function join($id) {

      $join = \app\models\Toner\ProductModelli::findOne(["id_prodotto" => $id, "id_modello" => $this->id]);
      if (!$join) {

        $join = new \app\models\Toner\ProductModelli();
        $join->id_prodotto = $id;
        $join->id_modello = $this->id;
        $join->disabled = 2;

        $join->save();

      }
      elseif($join->disabled==1) {
        $join->disabled=0;
        $join->save();
      }

    }

    public function Unjoin($id) {

      $join = \app\models\Toner\ProductModelli::findOne(["id_prodotto" => $id, "id_modello" => $this->id]);
      if ($join) {

        if ($join->disabled==2) {
          $join->delete();
          return;
        }

        if ($join->disabled==0) {
          $join->disabled = 1;
          $join->save();
          return;
        }

      }

    }

    public function beforeSave($insert) {

      if ($this->serie && ! $this->id_serie) {
        $id_marca = Marche::findOne(["nome" => $this->marca])->id;
        $this->id_serie = Serie::findOne(["nome" => $this->serie, "id_marca" => $id_marca])->id;
      }

      return parent::beforeSave($insert);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSerie()
    {
        return $this->hasOne(Serie::className(), ['id' => 'id_serie']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductModelli()
    {
        return $this->hasMany(ProductModelli::className(), ['id_modello' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'id_prodotto'])->viaTable('toner_product_modelli', ['id_modello' => 'id']);
    }

    public function setTipologia() {

      if ($this->tipologia)
        return;

      $prodotto = Product::find()
        ->innerJoin("toner_product_modelli", "toner_product_modelli.id_prodotto = toner_product.id")
        ->andWhere(["id_modello" => $this->id, "toner_product_modelli.disabled" => 0])
        ->andWhere(['not', ['toner_product.tipologia' => null]])
        ->andWhere(['enabled' => 1])
        ->addSelect("tipologia")
        ->asArray(true)
        ->distinct(true)
        ->all();

      if (sizeof($prodotto)==0)
        return;

      // Recupero le tipologie con gli alias
      $alias = [
        "TAMBURO" => "TONER",
        "CARTUCCIA" => "CARTUCCE"
      ];

      $values = array();
      foreach ($prodotto as $key => $value) {
        if (isset($alias[$value["tipologia"]]))
          $tipologia = $alias[$value["tipologia"]];
        else
          $tipologia = $value["tipologia"];
        $values[$tipologia] = $tipologia;
      }

      // verifica multipak
      if (sizeof($values)>1 && isset($values["MULTIPACK"]))
        unset ($values["MULTIPACK"]);

      if(sizeof($values)>1) {
        //echo "Più tipologie trovate [$this->nome] [$this->serie] [$this->marca]\n";

        \app\models\Message::add("moretipoligie_$this->id", "WARNING", "Più tipologie trovate [$this->nome] [$this->serie] [$this->marca]", "/toner-modelli/view?id=$this->id");

        return;
      }

      $tipologia = current($values);

      if ($tipologia=="MULTIPACK") {
        \app\models\Message::add("multipack_$this->id", "WARNING", "Tipologia unica MULTIPACK trovata [$this->nome] [$this->serie] [$this->marca]", "/toner-modelli/view?id=$this->id");
        return;
      }

      if ($tipologia) {
        if ($tipologia != $this->tipologia) {
          echo "Tipologia modello modificata [$this->nome] [$this->tipologia] => [$tipologia]\n";
          $this->tipologia = $tipologia;
          $this->save();
          //echo "Tipologia salvata modello [$this->nome]\n";
        }
      }

    }

    public function setEnabled() {

      $n = Product::find()
        ->innerJoin("toner_product_modelli", "toner_product_modelli.id_prodotto = toner_product.id")
        ->andWhere(["id_modello" => $this->id, "toner_product.enabled" => 1, "toner_product_modelli.disabled" => 0])
        ->count();

      if ($n>0)
        $enabled = 1;
      else
        $enabled = 0;

      if ($enabled != $this->enabled) {
        echo "Modello abilitato [$this->nome] [$this->enabled] => [$enabled]\n";
        $this->enabled = $enabled;
        $this->save();
      }


    }

    public function downloadPhoto() {

      if (!$this->photo)
        return;

      // Nome del file
      if ($this->serie == "ALTRI MODELLI")
        $dest = "/photo/modelli/".strtolower("{$this->marca}-{$this->nome}.jpg");
      else
        $dest = "/photo/modelli/".strtolower("{$this->marca}-{$this->serie}-{$this->nome}.jpg");

      $dest = str_replace(" ", "-", $dest);

      // Verifico se la foto dovrebbe essere gia stata scaricata
      if (strpos($this->photo, "/photo/modelli/")!==false) {

        if ($this->photo != $dest) {
          $this->photo = $dest;
          $this->save();
        }

        $img = "/opt/galileo$this->photo";

        if (!file_exists($img)) {
          echo "Foto non esistente [$this->marca] [$this->serie] [$this->nome] [$img]\n";
          $this->photo = null;
          $this->save();
        }
        else {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $info = finfo_file($finfo, $img);
          finfo_close($finfo);

          if ($info == "text/html" || $info == "inode/x-empty") {

            echo "File esistente ma non è una foto [$this->marca] [$this->serie] [$this->nome] [$img]\n";

            unlink($img);

            $this->photo = null;
            $this->save();

          }
        }

      }
      else {

        echo "Foto da scaricare [$this->marca] [$this->serie] [$this->nome] [$this->photo]\n";

        $source = $this->photo;
        $proxy = new \app\components\Proxy();
        $proxy->downloadFile($source, "/opt/galileo$dest");

        $img = "/opt/galileo$dest";

        if (file_exists($img)) {
          $this->photo = $dest;
          $this->save();
        }

      }

    }

    public function getImpression() {

      $command = Yii::$app->db->createCommand("SELECT sum(impression) FROM toner_adwords where keyword like '%{$this->serie}%{$this->nome}%'");
      return $command->queryScalar();

    }

    public function getStampapeSeoState() {

      $n = 0;

      if ($this->stampape_title)
        $n++;

      if ($this->stampape_metatitle)
        $n++;

      if ($this->stampape_description)
        $n++;

      if ($this->stampape_metadescription)
        $n++;

      return "$n/4";

    }

    public function getKeywords() {

      $command = Yii::$app->db->createCommand("SELECT * FROM toner_adwords where keyword like '%{$this->serie}%{$this->nome}%' order by impression desc");
      return $command->queryAll();

    }

}
