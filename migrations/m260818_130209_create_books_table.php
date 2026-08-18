<?php
use yii\db\Migration;

class m260818_130209_create_books_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'year' => $this->integer(4)->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(50)->null()->unique(),
            'photo' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%book}}');
    }
}
