<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Медиатека — Каталог</title>
    <style>
        body { font-family: sans-serif; max-width: 960px; margin: 20px auto; padding: 0 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; }
        th { background: #f4f4f4; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .badge-yes { color: green; font-weight: bold; }
        .badge-no { color: #b00; font-weight: bold; }
        .actions { display: flex; gap: 6px; }
    </style>
</head>
<body>
    <h1>Каталог медиатеки</h1>

    <div class="toolbar">
        <form method="get" action="index.php">
            <input type="hidden" name="action" value="index">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Поиск по названию или коду...">
            <select name="sort">
                <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>По названию</option>
                <option value="catalog_no" <?= $sort === 'catalog_no' ? 'selected' : '' ?>>По каталожному №</option>
                <option value="type" <?= $sort === 'type' ? 'selected' : '' ?>>По типу</option>
                <option value="available" <?= $sort === 'available' ? 'selected' : '' ?>>По доступности</option>
                <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>По дате</option>
            </select>
            <button type="submit">Найти</button>
            <?php if ($search !== ''): ?>
                <a href="index.php?action=index">Сбросить</a>
            <?php endif; ?>
        </form>

        <a href="index.php?action=create"><strong>+ Добавить объект</strong></a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Каталожный №</th>
                <th>Название</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items === []): ?>
                <tr><td colspan="6">Записи не найдены.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td><?= (int) $row['id'] ?></td>
                    <td><?= e($row['catalog_no']) ?></td>
                    <td><a href="index.php?action=show&id=<?= (int) $row['id'] ?>"><?= e($row['title']) ?></a></td>
                    <td><?= e($row['type']) ?></td>
                    <td>
                        <?php if ((int) $row['available'] === 1): ?>
                            <span class="badge-yes">Доступен</span>
                        <?php else: ?>
                            <span class="badge-no">Выдан</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a href="index.php?action=edit&id=<?= (int) $row['id'] ?>">Изменить</a>
                        <form method="post" action="index.php?action=delete&id=<?= (int) $row['id'] ?>" onsubmit="return confirm('Удалить запись?');">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p>
        <?php if ($page > 1): ?>
            <a href="index.php?action=index&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page - 1 ?>">&larr; Назад</a>
        <?php endif; ?>
        <span>Страница <?= (int) $page ?></span>
        <?php if (count($items) === 10): ?>
            <a href="index.php?action=index&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&page=<?= $page + 1 ?>">Вперёд &rarr;</a>
        <?php endif; ?>
    </p>
</body>
</html>
