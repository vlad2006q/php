<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/session.php';
requireLogin();

// Allow-list для настройки семестра (Cookie-настройка Варианта 16)
$allowedSemesters = ['2025-2026-1', '2025-2026-2', '2026-2027-1'];
$semester = $_COOKIE['semester'] ?? '2025-2026-2';

if (!is_string($semester) || !in_array($semester, $allowedSemesters, true)) {
    $semester = '2025-2026-2';
}

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;

    if (is_string($token) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Сохранение cookie выбора семестра
        if (isset($_POST['semester'])) {
            $semValue = $_POST['semester'];
            if (is_string($semValue) && in_array($semValue, $allowedSemesters, true)) {
                setcookie('semester', $semValue, [
                    'expires' => time() + 2592000,
                    'path' => '/',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
                header('Location: /dashboard.php');
                exit;
            }
        }

        // Защищённое действие преподавателя (отметка присутствия)
        if ($_SESSION['role'] === 'teacher' && isset($_POST['mark_presence'])) {
            $successMessage = 'Посещаемость успешно отмечена.';
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
</head>
<body>
    <h1>Личный кабинет (Учёт посещаемости)</h1>
    <p><strong>Пользователь:</strong> <?= h((string) $_SESSION['email']) ?></p>
    <p><strong>Роль:</strong> <?= h((string) $_SESSION['role']) ?></p>
    <p><strong>Выбранный семестр (из Cookie):</strong> <?= h($semester) ?></p>

    <!-- Форма настройки семестра через Cookie -->
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <label>Семестр:
            <select name="semester">
                <option value="2025-2026-1" <?= $semester === '2025-2026-1' ? 'selected' : '' ?>>2025-2026 / 1</option>
                <option value="2025-2026-2" <?= $semester === '2025-2026-2' ? 'selected' : '' ?>>2025-2026 / 2</option>
                <option value="2026-2027-1" <?= $semester === '2026-2027-1' ? 'selected' : '' ?>>2026-2027 / 1</option>
            </select>
        </label>
        <button type="submit">Изменить семестр</button>
    </form>

    <hr>

    <!-- Разделение функционала по ролям (Вариант 16) -->
    <?php if ($_SESSION['role'] === 'student'): ?>
        <h2>Мои посещения</h2>
        <ul>
            <li>Программирование на PHP: <b>Присутствовал</b></li>
            <li>Базы данных: <b>Присутствовал</b></li>
        </ul>
    <?php elseif ($_SESSION['role'] === 'teacher'): ?>
        <h2>Панель преподавателя</h2>
        <?php if ($successMessage !== ''): ?>
            <p style="color: green;"><?= h($successMessage) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="mark_presence" value="1">
            <button type="submit">Отметить присутствие студентов</button>
        </form>
    <?php endif; ?>

    <hr>

    <!-- Выход -->
    <form method="post" action="/logout.php">
        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
        <button type="submit" style="color: red;">Выйти из системы</button>
    </form>
</body>
</html>