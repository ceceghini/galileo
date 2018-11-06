<?php

namespace app\models\Toner;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Product;

/**
 * ProductSearch represents the model behind the search form of `app\models\Toner\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'id_verdestampa', 'originale_disponibile', 'manuale', 'id_marca', 'compatibile', 'originale', 'elaborato'], 'integer'],
            [['sku', 'ean', 'tipologia', 'colore', 'resa', 'originale_url_foto', 'compatibile_url_foto', 'part_number', 'url'], 'safe'],
            [['originale_prezzo', 'compatibile_prezzo'], 'number'],
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
            'id' => $this->id,
            'enabled' => $this->enabled,
            'id_verdestampa' => $this->id_verdestampa,
            'originale_prezzo' => $this->originale_prezzo,
            'originale_disponibile' => $this->originale_disponibile,
            'compatibile_prezzo' => $this->compatibile_prezzo,
            'manuale' => $this->manuale,
            'id_marca' => $this->id_marca,
            'compatibile' => $this->compatibile,
            'originale' => $this->originale,
            'elaborato' => $this->elaborato,
        ]);

        $query->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'ean', $this->ean])
            ->andFilterWhere(['like', 'tipologia', $this->tipologia])
            ->andFilterWhere(['like', 'colore', $this->colore])
            ->andFilterWhere(['like', 'resa', $this->resa])
            ->andFilterWhere(['like', 'originale_url_foto', $this->originale_url_foto])
            ->andFilterWhere(['like', 'compatibile_url_foto', $this->compatibile_url_foto])
            ->andFilterWhere(['like', 'part_number', $this->part_number])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}
