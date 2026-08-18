<?php
use yii\db\Migration;

class m260818_130226_create_books_authors_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%book_author}}', [
            'book_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_book_author', '{{%book_author}}', ['book_id', 'author_id']);

        $this->addForeignKey(
            'fk_book_author_book',
            '{{%book_author}}',
            'book_id',
            '{{%book}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_book_author_author',
            '{{%book_author}}',
            'author_id',
            '{{%author}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_book_author_book', '{{%book_author}}');
        $this->dropForeignKey('fk_book_author_author', '{{%book_author}}');
        $this->dropTable('{{%book_author}}');
    }
}
