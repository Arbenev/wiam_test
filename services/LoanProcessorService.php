<?php

namespace app\services;

use Yii;
use app\models\LoanRequest;
use yii\db\IntegrityException;

class LoanProcessorService
{
    public function process(int $delay, int $limit = 100): void
    {
        $processed = 0;

        while ($processed < $limit) {
            $request = $this->lockNextNewRequest();
            if ($request === null) {
                break;
            }

            // Эмуляция долгой обработки
            sleep($delay);

            $approved = (random_int(1, 10) === 1); // 10%

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $request->refresh(); // на всякий случай, если что-то изменилось

                if ($approved) {
                    $request->status = 'approved';
                } else {
                    $request->status = 'declined';
                }

                $request->processed_at = time();

                try {
                    $request->save(false);
                } catch (IntegrityException $e) {
                    // Нарушение partial unique index: уже есть approved-заявка
                    $request->status = 'declined';
                    $request->save(false);
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                // можно логировать
            }

            $processed++;
        }
    }

    private function lockNextNewRequest(): ?LoanRequest
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // raw SQL с FOR UPDATE SKIP LOCKED
            $row = $db->createCommand("
                SELECT *
                FROM loan_request
                WHERE status = 'new'
                ORDER BY id
                FOR UPDATE SKIP LOCKED
                LIMIT 1
            ")->queryOne();

            if (!$row) {
                $transaction->rollBack();
                return null;
            }

            /** @var LoanRequest $request */
            $request = LoanRequest::findOne($row['id']);
            $request->status = 'processing';
            $request->save(false);

            $transaction->commit();
            return $request;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return null;
        }
    }
}
