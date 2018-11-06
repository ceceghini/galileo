<?php

namespace app\models\Toner\Source;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Source\Modelli;

/**
 * ModelliSearch represents the model behind the search form of `app\models\Toner\Source\Modelli`.
 */
class ModelliSearch extends Modelli
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome', 'serie', 'marca', 'source', 'source_key', 'elaborato', 'is_present'], 'safe'],
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
        $query = Modelli::find();

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
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'serie', $this->serie])
            ->andFilterWhere(['like', 'marca', $this->marca])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'source_key', $this->source_key]);

        return $dataProvider;
    }
}
