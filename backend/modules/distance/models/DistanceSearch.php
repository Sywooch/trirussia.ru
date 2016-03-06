<?php

namespace distance\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use distance\models\Distance;

/**
 * DistanceSearch represents the model behind the search form about `distance\models\Distance`.
 */
class DistanceSearch extends Distance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sport_id'], 'integer'],
            [['label'], 'safe'],
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
        $query = Distance::find();

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
            'sport_id' => $this->sport_id,
        ]);

        $query->andFilterWhere(['like', 'label', $this->label]);

        $query->orderBy('id DESC');

        return $dataProvider;
    }
}
