<?php

namespace app\models\Toner;

use Yii;

/**
 * This is the model class for table "toner_product".
 *
 * @property int $id
 * @property string $sku
 * @property string $ean
 * @property int $enabled
 * @property int $id_verdestampa
 * @property string $tipologia
 * @property string $colore
 * @property string $resa
 * @property string $originale_url_foto
 * @property string $compatibile_url_foto
 * @property string $originale_prezzo
 * @property int $originale_disponibile
 * @property string $compatibile_prezzo
 * @property int $manuale
 * @property int $id_marca
 * @property string $part_number
 * @property int $compatibile
 * @property int $originale
 *
 * @property Product $alias
 * @property Product[] $products
 * @property TonerProductModelli[] $tonerProductModellis
 * @property TonerModelli[] $modellos
 * @property TonerProductSale[] $tonerProductSales
 */
class Product extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'tipologia'], 'required'],
            [['enabled', 'id_verdestampa', 'originale_disponibile', 'manuale', 'id_marca', 'compatibile', 'originale', 'secondario'], 'integer'],
            [['originale_prezzo', 'compatibile_prezzo', 'compatibile_prezzo_tonerper', 'compatibile_prezzo_ecolors'], 'number'],
            [['sku'], 'string', 'max' => 32],
            [['ean'], 'string', 'max' => 45],
            [['tipologia', 'colore', 'resa', 'originale_url_foto', 'compatibile_url_foto', 'part_number'], 'string', 'max' => 255],
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
            'sku' => 'Sku',
            'ean' => 'Ean',
            'enabled' => 'Enabled',
            'id_verdestampa' => 'Id Verdestampa',
            'tipologia' => 'Tipologia',
            'colore' => 'Colore',
            'resa' => 'Resa',
            'originale_url_foto' => 'Originale Url Foto',
            'compatibile_url_foto' => 'Compatibile Url Foto',
            'originale_prezzo' => 'Originale Prezzo',
            'originale_disponibile' => 'Originale Disponibile',
            'compatibile_prezzo' => 'Compatibile Prezzo',
            'compatibile_prezzo_tonerper' => 'Compatibile Prezzo tonerpertutti.it',
            'compatibile_prezzo_ecolors' => 'Compatibile Prezzo ecolors.it',
            'manuale' => 'Manuale',
            'id_marca' => 'Id Marca',
            'part_number' => 'Part Number',
            'compatibile' => 'Compatibile',
            'originale' => 'Originale',
        ];
    }

    public function beforeSave($insert) {

      if ($this->compatibile_prezzo > 0)
        $this->compatibile = 1;
      else
        $this->compatibile = 0;

      if ($this->originale_prezzo > 0)
        $this->originale = 1;
      else
        $this->originale = 0;

      if ($this->compatibile && !$this->ean) {
        $this->ean = \Yii::$app->db->createCommand("select ean from ean e where not exists (select 1 from toner_product p where p.ean = e.ean)")->queryScalar();
      }

      /*if ($this->manuale)
        return parent::beforeSave($insert);*/

      if (($this->originale || $this->compatibile))
        $this->enabled = 1;
      else
        $this->enabled = 0;

      return parent::beforeSave($insert);

    }

    public function copyModelli($id) {

      foreach ($this->modelli as $modello) {

        $join = new \app\models\Toner\ProductModelli();
        $join->id_prodotto = $id;
        $join->id_modello = $modello->id;
        $join->save();

      }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarca()
    {
        return $this->hasOne(Marche::className(), ['id' => 'id_marca']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductModelli()
    {
        return $this->hasMany(ProductModelli::className(), ['id_prodotto' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelli()
    {
        return $this->hasMany(Modelli::className(), ['id' => 'id_modello'])->viaTable('toner_product_modelli', ['id_prodotto' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceProducts()
    {
        return $this->hasMany(\app\models\Toner\Source\Product::className(), ['id' => 'id_source_product'])->viaTable('toner_source_product_join', ['id_product' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceProductsJoin()
    {
        return $this->hasMany(ProductJoin::className(), ['id_product' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getproductSales()
    {
        return $this->hasMany(ProductSale::className(), ['id_product' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getproductPrezzi()
    {
        return $this->hasMany(ProductPrezzi::className(), ['id_product' => 'id']);
    }

    public static function getTipologie($select = null) {

      $tipologie = \Yii::$app->db->createCommand("select distinct tipologia from toner_product")->queryColumn();

      if ($select)
        $ret = array();
      else
        $ret = array(null => "...");

      foreach ($tipologie as $value) {
        $ret[$value] = $value;
      }

      return $ret;

    }

    public function getSource($source) {

      $n = $this->getCountSource($source);

      // Nessun sorgente collegato
      if (!$n)
        return;

      // 1 sorgente collegato
      if ($n==1) {
        $sql = "select s.qty, s.html, s.price
                  from toner_source_product s
                    join toner_source_product_join j on s.id = j.id_source_product
                 where j.id_product = :id_product and s.source = :source and j.disabled <> 1";

        $source = \Yii::$app->db->createCommand($sql)
          ->bindValue(":id_product", $this->id)
          ->bindValue(":source", $source)
          ->queryOne();

        return $source;
      }
      else {
        $sql = "select avg(price) as price
                  from toner_source_product s
                    join toner_source_product_join j on s.id = j.id_source_product
                 where j.id_product = :id_product and s.source = :source
                   and j.disabled <> 1
                having min(price) / max(price) >= 0.9";

        $source = \Yii::$app->db->createCommand($sql)
          ->bindValue(":id_product", $this->id)
          ->bindValue(":source", $source)
          ->queryOne();

        if ($source) {
          $source["qty"] = 1;
          $source["html"] = "";
        }

        return $source;
      }

    }

    private function getCountSource($source) {

      $sql = "select count(*)
                from toner_source_product s
                  join toner_source_product_join j on s.id = j.id_source_product
               where j.id_product = :id_product and s.source = :source and j.disabled <> 1";

      $n = \Yii::$app->db->createCommand($sql)
        ->bindValue(":id_product", $this->id)
        ->bindValue(":source", $source)
        ->queryScalar();

      return $n;

    }

    public function getPrezzoVS($negozio) {

      $sql = "select prezzo from toner_product_prezzi where negozio = :negozio and id_product = :id_product";

      $_price = \Yii::$app->db->createCommand($sql)
        ->bindValue(":id_product", $this->id)
        ->bindValue(":negozio", $negozio)
        ->queryScalar();
      return $_price;
    }

    public function getPrezzoMedioVs() {
      $_price = \Yii::$app->db->createCommand("select avg(prezzo) as prezzo from toner_product_prezzi where id_product = :id_product having count(*) >= 3")
        ->bindValue(":id_product", $this->id)
        ->queryScalar();

      return $_price;
    }

    public function getPrezzoMinVs() {
      $_price = \Yii::$app->db->createCommand("select min(prezzo) as prezzo from toner_product_prezzi where id_product = :id_product having count(*) >= 3")
        ->bindValue(":id_product", $this->id)
        ->queryScalar();

      return $_price;
    }

    public function getPrezzoMinVs2() {
      $_price1 = \Yii::$app->db->createCommand("select min(prezzo) as prezzo from toner_product_prezzi where id_product = :id_product having count(*) >= 3")
        ->bindValue(":id_product", $this->id)
        ->queryScalar();

      if (!$_price1)
        return;

      $_price = \Yii::$app->db->createCommand("select min(prezzo) as prezzo from toner_product_prezzi where id_product = :id_product and prezzo <> :prezzo having count(*) >= 3")
        ->bindValue(":id_product", $this->id)
        ->bindValue(":prezzo", $_price1)
        ->queryScalar();

      return $_price;
    }

    public function getStampapeSeoState() {

      $products = \app\models\Toner\Modelli::find();
      $products->joinWith("products")
        ->andWhere(["id_prodotto" => $this->id])
        ->all();

      $n = 0;
      $i = 0;
      foreach ($products as $key => $value) {
        $n ++;
        if ($value->getStampapeSeoState()=="4/4")
          $i++;
      }

      return "$i/$n";

    }

}
