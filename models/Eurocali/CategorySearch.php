<?php

namespace app\models\Eurocali;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Eurocali\Category;

/**
 * CategorySearch represents the model behind the search form about `app\models\Eurocali\Category`.
 */
class CategorySearch extends Category
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'present', 'elaborato', 'id_parent'], 'integer'],
            [['title', 'url', 'description', 'img'], 'safe'],
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
        $query = Category::find();

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
            'id_parent' => $this->id_parent,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'img', $this->img]);

        return $dataProvider;
    }
}
