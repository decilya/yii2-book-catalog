<?php
namespace app\commands;

use yii\console\Controller;
use app\models\Author;

class SeedController extends Controller
{
    public function actionAuthors()
    {
        $names = ['Лев Толстой', 'Фёдор Достоевский', 'Антон Чехов'];
        foreach ($names as $name) {
            $model = new Author();
            $model->full_name = $name;
            if ($model->save()) {
                echo "✅ Добавлен автор: $name\n";
            } else {
                echo "❌ Ошибка: $name\n";
            }
        }
    }
}
