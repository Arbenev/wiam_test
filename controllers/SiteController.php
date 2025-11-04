<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\db\Query;
class SiteController extends Controller
{

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = new Query();
        $query->select('*')->from('loan_request')->orderBy(['id' => SORT_ASC]);
        $requests = $query->all();
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $requests,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'attributes' => ['id', 'status', 'create_at'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
