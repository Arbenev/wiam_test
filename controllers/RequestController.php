<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use \app\services\LoanProcessorService;

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

        return (new LoanProcessorService())->process($body);
    }
}
