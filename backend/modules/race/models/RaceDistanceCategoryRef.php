<?php

namespace race\models;

use distance\models\DistanceCategory;
use Yii;

/**
 * This is the model class for table "race_distance_category_ref".
 *
 * @property integer $id
 * @property integer $race_id
 * @property integer $distance_category_id
 */
class RaceDistanceCategoryRef extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'race_distance_category_ref';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['race_id', 'distance_category_id'], 'required'],
            [['race_id', 'distance_category_id'], 'integer'],
            [['distance_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => DistanceCategory::className(), 'targetAttribute' => ['distance_category_id' => 'id']],
            [['race_id'], 'exist', 'skipOnError' => true, 'targetClass' => Race::className(), 'targetAttribute' => ['race_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'race_id' => 'Race ID',
            'distance_category_id' => 'Distance Category ID',
        ];
    }
}
