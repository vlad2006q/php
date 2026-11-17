<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Карточка: <?= e($item['title']) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 20px auto; padding: 0 15px; }
        dl { display: grid; grid-template-columns: 180px 1fr; gap: 8px; }
        dt { font-weight: bold; }
        .bar { margin-top: 20px; display: flex; gap: 10px; align-items: center; }
    </style>
</head>
<body>
    <h1><?= e($item['title']) ?></h1>

    <dl>
        <dt>ID:</dt>
        <dd><?= (int) $item['id'] ?></dd>

        <dt>Каталожный номер:</dt>
        <dd><?= e($item['catalog_no']) ?></dd>

        <dt>Тип:</dt>
        <dd><?= e($item['type']) ?></dd>

        <dt>Статус:</dt>
        <dd><?= (int) $item['available'] === 1 ? 'Доступен' : 'Выдан' ?></dd>

        <dt>Создан:</dt>
        <dd><?= e($item['created_at']) ?></dd>

        <dt>Обновлён:</dt>
        <dd><?= e($item['updated_at']) ?></dd>
    </dl>

    <div class="bar">
        <?php if ((int) $item['available'] === 1): ?>
            <form method="post" action="index.php?action=issue&id=<?= (int) $item['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                <button type="submit">Выдать объект (Транзакция + Аудит)</button>
            </form>
        <?php endif; ?>

        <a href="index.php?action=edit&id=<?= (int) $item['id'] ?>">Редактировать</a>
        <a href="index.php?action=index">К списку</a>
    </div>
</body>
</html>
