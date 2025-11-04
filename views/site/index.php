<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Запросы на займы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-index">
    <?php

    ?>
    <h1>
        <?= Html::encode($this->title) ?>
    </h1>

    <?php if (isset($dataProvider)) : ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'pager' => [ 'linkOptions' => ['style' => 'margin:0 8px;'] ],
            'columns' => [
                ['attribute' => 'id', 'label' => 'ID'],
                ['attribute' => 'user_id', 'label' => 'User ID'],
                ['attribute' => 'amount', 'label' => 'Amount'],
                ['attribute' => 'term', 'label' => 'Term'],
                ['attribute' => 'status', 'label' => 'Status'],
                ['attribute' => 'created_at', 'label' => 'Created At', 'format' => ['date', 'php:Y-m-d H:i:s']],
                ['attribute' => 'updated_at', 'label' => 'Updated At', 'format' => ['date', 'php:Y-m-d H:i:s']],
                ['attribute' => 'processed_at', 'label' => 'Processed At', 'format' => ['date', 'php:Y-m-d H:i:s']],
            ],
        ]) ?>
    <?php else: ?>
        <p>Нет данных для отображения.</p>
    <?php endif; ?>
</div>