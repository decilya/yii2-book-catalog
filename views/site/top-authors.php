<?php
use yii\helpers\Html;
?>
<h2>ТОП-10 авторов за <?= Html::encode($year) ?> год</h2>
<table class="table table-striped">
    <thead><tr><th>#</th><th>Автор</th><th>Количество книг</th></tr></thead>
    <tbody>
    <?php foreach ($top as $index => $row): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= Html::encode($row['full_name']) ?></td>
            <td><?= $row['book_count'] ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($top)): ?><tr><td colspan="3">Нет данных за этот год</td></tr><?php endif; ?>
    </tbody>
</table>
<form method="get" class="form-inline">
    <input type="number" name="year" value="<?= $year ?>" class="form-control" style="width: 100px;">
    <button type="submit" class="btn btn-default">Показать</button>
</form>
