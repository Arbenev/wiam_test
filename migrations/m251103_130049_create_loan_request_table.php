<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%loan_request}}`.
 */
class m251103_130049_create_loan_request_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%loan_request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull(),
            'term' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('new'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'processed_at' => $this->integer()->null(),
        ]);

        $this->createIndex(
            'idx_loan_request_user_id',
            '{{%loan_request}}',
            'user_id'
        );

        $this->createIndex(
            'idx_loan_request_status',
            '{{%loan_request}}',
            'status'
        );

        // Ограничение: не более одной approved-заявки на пользователя
        $this->execute(
            "CREATE UNIQUE INDEX ux_loan_request_user_approved
         ON loan_request (user_id)
         WHERE status = 'approved';"
        );
    }

    public function safeDown()
    {
        $this->execute("DROP INDEX IF EXISTS ux_loan_request_user_approved;");
        $this->dropIndex('idx_loan_request_status', '{{%loan_request}}');
        $this->dropIndex('idx_loan_request_user_id', '{{%loan_request}}');
        $this->dropTable('{{%loan_request}}');
    }
}
