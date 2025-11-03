<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\services\LoanProcessorService;

class ProcessorController extends Controller
{
    public $enableCsrfValidation = false;

    private LoanProcessorService $service;

    public function __construct($id, $module, LoanProcessorService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $delay = (int)Yii::$app->request->get('delay', 5);
        if ($delay < 0) {
            $delay = 0;
        }

        $this->service->process($delay, 100);

        return ['result' => true];
    }
}
