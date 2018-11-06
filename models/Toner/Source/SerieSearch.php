<?php

namespace app\models\Toner\Source;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Source\Serie;

/**
 * SerieSearch represents the model behind the search form of `app\models\Toner\Source\Serie`.
 */
class SerieSearch extends Serie
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_source_marca', 'id_serie', 'is_present'], 'integer'],
            [['nome', 'source', 'source_key'], 'safe'],
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

        if ($this->id_serie!=null) {
          if ($this->id_serie == 0)
            $query->andWhere(['id_serie' => null]);
          else
            $query->andWhere(['is not', 'id_serie', null]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'source' => $this->source,
            'id_source_marca' => $this->id_source_marca,
            'is_present' => $this->is_present,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'source_key', $this->source_key]);

        return $dataProvider;
    }
}
