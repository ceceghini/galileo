<?php

namespace app\models\Toner;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Modelli;

/**
 * TonerModelliSearch represents the model behind the search form about `app\models\TonerModelli`.
 */
class ModelliSearch extends Modelli
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['enabled', 'id_verdestampa'], 'integer'],
            [['nome', 'serie', 'marca'], 'safe'],
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

        if ($this->id_verdestampa!=null) {
          if ($this->id_verdestampa == 0)
            $query->andWhere(['id_verdestampa' => 0]);
          else
            $query->andWhere(['>', 'id_verdestampa', 0]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'serie', $this->serie])
            ->andFilterWhere(['like', 'marca', $this->marca]);

        return $dataProvider;
    }
}
