<?php

namespace app\models\Toner\Source;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Toner\Source\Marche;

/**
 * MarcheSearch represents the model behind the search form of `app\models\Toner\Source\Marche`.
 */
class MarcheSearch extends Marche
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nome', 'source', 'source_key', 'id_marca'], 'safe'],
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
        $query = Marche::find();

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

        if ($this->id_marca!=null) {
          if ($this->id_marca == 0)
            $query->andWhere(['id_marca' => null]);
          else
            $query->andWhere(['is not', 'id_marca', null]);
        }

        $query->andFilterWhere([
            'source' => $this->source,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'source_key', $this->source_key]);

        return $dataProvider;
    }
}
