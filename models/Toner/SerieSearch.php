<?php

namespace app\models\Toner;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Serie;

/**
 * SerieSearch represents the model behind the search form of `app\models\Toner\Serie`.
 */
class SerieSearch extends Serie
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_marca', 'id_verdestampa'], 'integer'],
            [['nome'], 'safe'],
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
        $query = Serie::find();

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
            'id_marca' => $this->id_marca,
            'id_verdestampa' => $this->id_verdestampa,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome]);

        return $dataProvider;
    }
}
