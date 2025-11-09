<?php

namespace app\services;

use stdClass;
use Yii;
use app\models\LoanRequest;


class LoanProcessorService
{
    public function process(stdClass $body): array
    {
        $model = new LoanRequest();
        $model->user_id = $body->user_id ?? null;
        $model->amount  = $body->amount ?? null;
        $model->term    = $body->term ?? null;
        $model->created_at = time();
        $model->updated_at = time();
        $model->status  = 'new';

        // Проверка наличия уже одобренных заявок
        if ($model->user_id !== null) {
            $hasApproved = LoanRequest::find()
                ->where([
                    'user_id' => $model->user_id,
                    'status'  => 'approved',
                ])
                ->exists();

            if ($hasApproved) {
                Yii::$app->response->statusCode = 400;
                return [
                    'result' => false,
                    'errors' => [
                        'user_id' => ['User already has an approved loan request.'],
                    ],
                ];
            }
        }

        if (!$model->validate()) {
            Yii::$app->response->statusCode = 400;
            return [
                'result' => false,
                'errors' => $model->getErrors(),
            ];
        }

        if (!$model->save(false)) {
            Yii::$app->response->statusCode = 500;
            return [
                'result' => false,
                'errors' => ['internal' => ['Failed to save loan request.']],
            ];
        }

        Yii::$app->response->statusCode = 201;
        return [
            'result' => true,
            'id'     => $model->id,
        ];
    }
}
