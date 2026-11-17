<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 20px auto; padding: 0 15px; }
        .field { margin-bottom: 14px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; }
        input[type="text"], select { width: 100%; padding: 6px; box-sizing: border-box; }
        .error { color: #b00; font-size: 0.9em; margin-top: 4px; }
    </style>
</head>
<body>
    <h1><?= e($pageTitle) ?></h1>

    <form method="post" action="<?= e($formAction) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div class="field">
            <label for="catalog_no">Каталожный номер (например, MED-101):</label>
            <input type="text" id="catalog_no" name="catalog_no" value="<?= e($item['catalog_no'] ?? '') ?>" required>
            <?php if (!empty($errors['catalog_no'])): ?>
                <div class="error"><?= e($errors['catalog_no']) ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" value="<?= e($item['title'] ?? '') ?>" maxlength="180" required>
            <?php if (!empty($errors['title'])): ?>
                <div class="error"><?= e($errors['title']) ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="type">Тип носителя:</label>
            <select id="type" name="type">
                <?php
                $types = ['book' => 'Книга (book)', 'video' => 'Видео (video)', 'audio' => 'Аудио (audio)', 'magazine' => 'Журнал (magazine)'];
                $currentType = $item['type'] ?? 'book';
                foreach ($types as $val => $label):
                ?>
                    <option value="<?= e($val) ?>" <?= $currentType === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['type'])): ?>
                <div class="error"><?= e($errors['type']) ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label>
                <input type="checkbox" name="available" value="1" <?= !empty($item['available']) ? 'checked' : '' ?>>
                Доступен к выдаче
            </label>
        </div>

        <button type="submit">Сохранить</button>
        <a href="index.php?action=index">Отмена</a>
    </form>
</body>
</html>
