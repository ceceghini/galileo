<?php

namespace app\models\Toner;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\ProductSale;

/**
 * ProductSaleSearch represents the model behind the search form of `app\models\Toner\ProductSale`.
 */
class ProductSaleSearch extends ProductSale
{

    public $sku;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['qty'], 'integer'],
            [['tipologia', 'period', 'sku'], 'safe'],
            [['total'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductSale::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_product' => $this->id_product,
            'qty' => $this->qty,
            'total' => $this->total,
            'toner_product_sale.tipologia' => $this->tipologia,
            'period' => $this->period,
        ]);

        $query->joinWith("product");

        $query->andFilterWhere(['like', 'toner_product.sku', $this->sku]);

        return $dataProvider;
    }
}
