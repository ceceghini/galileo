<?php

namespace app\models\Toner\Source;

use Yii;

/**
 * This is the model class for table "toner_source_product".
 *
 * @property int $id
 * @property string $sku
 * @property string $title
 * @property string $price
 * @property string $color
 * @property string $description
 * @property string $source
 * @property string $source_key
 * @property int $elaborato
 * @property int $disabled
 * @property resource $html
 */
class Product extends \yii\db\ActiveRecord
{

    private $mapcolor = [
      "Nero" => "NERO",
      "Colori" => "COLORI",
      "Ciano" => "CIANO",
      "Magenta" => "MAGENTA",
      "Giallo" => "GIALLO",
      "Magenta Fotografico" => "MAGENTA PHOTO",
      "Ciano Fotografico" => "CIANO PHOTO",
      "Rosso" => "ROSSO",
      "Verde" => "VERDE",
      "Nero Matte" => "NERO MATTE",
      "Nero Fotografico" => "NERO PHOTO",
      "Blu" => "BLU",
      "Grigio" => "GRIGIO",
      "Grigio Foto" => "GRIGIO PHOTO",
      "Nero + Colore" => "NERO+COLORE",
      "Nero + Nero + Colori" => "NERO+COLORE",
      "Nero Light" => "NERO CHIARO",
      "Arancione" => "ARANCIO",
      "Trasparente" => "TRASPARENTE",
      "Nero Light Light" => "NERO CHIARO CHIARO",
      "VIVID MAGENTA" => "VIVID MAGENTA",
      "VIVID LIGHT MAGENTA" => "VIVID LIGHT MAGENTA",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'toner_source_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source'], 'required'],
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
            'title' => 'Title',
            'price' => 'Price',
            'color' => 'Color',
            'description' => 'Description',
            'source' => 'Source',
            'source_key' => 'Source Key',
            'elaborato' => 'Elaborato',
            'disabled' => 'Disabled',
            'html' => 'Html',
        ];
    }

    public function joinModelli($id) {

      foreach ($this->modelli as $modello) {

        $join = new \app\models\Toner\ProductModelli();
        $join->id_prodotto = $id;
        $join->id_modello = $modello->id_modello;
        $join->save();

      }

    }

    public function join($id) {

      $join = \app\models\Toner\ProductJoin::findOne(["id_product" => $id, "id_source_product" => $this->id]);
      if (!$join) {

        $join = new \app\models\Toner\ProductJoin();
        $join->id_product = $id;
        $join->id_source_product = $this->id;
        $join->disabled = 2;

        $join->save();

      }
      elseif($join->disabled==1) {
        $join->disabled=0;
        $join->save();
      }

    }

    public function Unjoin($id) {

      $join = \app\models\Toner\ProductJoin::findOne(["id_product" => $id, "id_source_product" => $this->id]);
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

    public function joinState($id_product) {

      $join = \app\models\Toner\ProductJoin::findOne(["id_product" => $id_product, "id_source_product" => $this->id]);

      if (!$join)
        return "";

      switch ($join->disabled) {
        case 0:
          return "Prodotto collegato automaticamente";
          break;
        case 1:
          return "Collegamento disabilitato";
          break;
        case 2:
          return "Prodotto collegato maualmente";
          break;
      }

    }

    public function isJoined($id) {

      $join = \app\models\Toner\ProductJoin::findOne(["id_product" => $id, "id_source_product" => $this->id]);

      if ($join)
        return $join->disabled;
      else
        return null;

    }

    public function getOtherSource() {

      if (!$this->sku)
        return;

      $sku = str_replace("-", "", $this->sku);

      $other = \Yii::$app->db->createCommand("
      select distinct substr(source, 1, 2)
        from toner_source_product
       where source <> :source
         and (
           replace(sku, '-', '') = :sku or
           replace(source_key, '-', '') like :sku2 or
           concat(' ', replace(title, '-', ''), ' ') like :sku3
           )
      ")
      ->bindValue(":source", $this->source)
      ->bindValue(":sku", $sku)
      ->bindValue(":sku2", "%/$sku")
      ->bindValue(":sku3", "% $sku %")
      ->queryColumn();

      return implode(" - ",$other);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelli()
    {
        return $this->hasMany(Modelli::className(), ['id' => 'id_modello'])->viaTable('toner_source_product_modelli', ['id_product' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceProductsJoin()
    {
        return $this->hasMany(\app\models\Toner\ProductJoin::className(), ['id_source_product' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(\app\models\Toner\Product::className(), ['id' => 'id_product'])->viaTable('toner_source_product_join', ['id_source_product' => 'id']);
    }

    public function getProductColor() {

      if (isset($this->mapcolor[$this->color]))
        return $this->mapcolor[$this->color];
      else
        return null;

    }

}
