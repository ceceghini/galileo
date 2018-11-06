<?php

namespace app\models\Toner\Source;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Source\Product;

/**
 * ProductSearch represents the model behind the search form of `app\models\Toner\Source\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['elaborato', 'disabled', 'is_present'], 'integer'],
            [['sku', 'title', 'color', 'source', "source_key"], 'safe'],
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
        $query = Product::find();

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
            'elaborato' => $this->elaborato,
            'is_present' => $this->is_present,
            'toner_source_product.disabled' => $this->disabled,
            'source' => $this->source,
        ]);

        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'source_key', $this->source_key])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'color', $this->color]);

        return $dataProvider;
    }
}
