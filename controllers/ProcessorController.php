<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\services\LoanProcessorService;

class ProcessorController extends Controller
{
    const MAX_PROCESSING = 100;
    const DEFAULT_DELAY = 5;

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
        $delay = (int)Yii::$app->request->get('delay', self::DEFAULT_DELAY);
        if ($delay < 0) {
            $delay = 0;
        }

        $result = $this->service->process($delay, self::MAX_PROCESSING);

        return ['result' => true, 'processed' => $result];
    }
}
