<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loan_request".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $term
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $processed_at
 */
class LoanRequest extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loan_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer', 'min' => 1],
            ['amount', 'integer', 'max' => 1000000], // верхний предел
            ['term', 'integer', 'max' => 365],       // верхний предел
            ['status', 'in', 'range' => ['new', 'processing', 'approved', 'declined']],
            [['processed_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'new'],
            [['user_id', 'amount', 'term', 'created_at'], 'required'],
            [['user_id', 'amount', 'term', 'created_at', 'updated_at', 'processed_at'], 'integer'],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'amount' => 'Amount',
            'term' => 'Term',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'processed_at' => 'Processed At',
        ];
    }
}
