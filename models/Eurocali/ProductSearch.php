<?php

namespace app\models\Eurocali;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Eurocali\Product;

/**
 * ProductSearch represents the model behind the search form about `app\models\Eurocali\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'present', 'elaborato'], 'integer'],
            [['url', 'brand', 'short_description', 'title', 'description', 'html', 'json_data'], 'safe'],
            [['price'], 'number'],
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'present' => $this->present,
            'elaborato' => $this->elaborato,
            'price' => $this->price,
        ]);

        $query->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'brand', $this->brand])
            ->andFilterWhere(['like', 'short_description', $this->short_description])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'html', $this->html])
            ->andFilterWhere(['like', 'json_data', $this->json_data]);

        return $dataProvider;
    }
}
