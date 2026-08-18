<?php
use yii\db\Migration;

class m260818_130247_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Создаём тестового пользователя admin / admin
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
            'auth_key' => \Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
