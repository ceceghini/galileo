<?php

namespace app\models\Toner\Report;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Report\PriceSale;

/**
 * MarcheSearch represents the model behind the search form of `app\models\Toner\Source\Marche`.
 */
class PriceSaleSearch extends PriceSale
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'qty', 'total', 'prezzo_avg', 'tipologia'], 'safe'],
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
        $query = PriceSale::find();

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

        if ($this->prezzo_avg == "0") {
            $query->andWhere([
              "prezzo_avg" => null
            ]);
          }

        $query->andFilterWhere([
            'sku' => $this->sku,
            'qty' => $this->qty,
            'total' => $this->total,
            'tipologia' => $this->tipologia,
        ]);

        return $dataProvider;
    }
}
