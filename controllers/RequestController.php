<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\models\LoanRequest;

class RequestController extends Controller
{
    public $enableCsrfValidation = false;

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionCreate()
    {
        $body = json_decode(Yii::$app->request->getRawBody());

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
