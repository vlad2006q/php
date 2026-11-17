<?php

declare(strict_types=1);

session_start();

// Загрузка переменных окружения из .env
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        putenv(trim($line));
    }
}

require __DIR__ . '/../vendor/autoload.php';

use App\Database\ConnectionFactory;
use App\Repository\MediaRepository;
use App\Service\MediaService;

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Ошибка безопасности: недействительный CSRF-токен.');
    }
}

try {
    $pdo = ConnectionFactory::create();
} catch (Throwable $e) {
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(503);
    exit('Сервис временно недоступен. Попробуйте позже.');
}

$service = new MediaService(new MediaRepository($pdo));

$action = $_GET['action'] ?? 'index';
$allowedActions = ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'issue'];

if (!in_array($action, $allowedActions, true)) {
    http_response_code(404);
    exit('Страница не найдена.');
}

$postActions = ['store', 'update', 'delete', 'issue'];
if (in_array($action, $postActions, true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Метод не разрешён.');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

switch ($action) {
    case 'index':
        $search = trim((string) ($_GET['search'] ?? ''));
        $sort = (string) ($_GET['sort'] ?? 'title');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $items = $service->list($search, $sort, $page, 10);
        require __DIR__ . '/../templates/media/index.php';
        break;

    case 'show':
        $item = $service->get($id);
        if ($item === null) {
            http_response_code(404);
            exit('Запись не найдена.');
        }
        require __DIR__ . '/../templates/media/show.php';
        break;

    case 'create':
        $item = ['catalog_no' => '', 'title' => '', 'type' => 'book', 'available' => 1];
        $errors = [];
        $formAction = 'index.php?action=store';
        $pageTitle = 'Добавить объект в медиатеку';
        require __DIR__ . '/../templates/media/form.php';
        break;

    case 'store':
        verifyCsrf();
        try {
            $result = $service->create($_POST);
            if ($result['errors'] !== []) {
                http_response_code(422);
                $item = $_POST;
                $errors = $result['errors'];
                $formAction = 'index.php?action=store';
                $pageTitle = 'Добавить объект в медиатеку';
                require __DIR__ . '/../templates/media/form.php';
                break;
            }
            header('Location: index.php?action=index', true, 303);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                $item = $_POST;
                $errors = ['catalog_no' => 'Объект с таким каталожным номером уже существует.'];
                $formAction = 'index.php?action=store';
                $pageTitle = 'Добавить объект в медиатеку';
                require __DIR__ . '/../templates/media/form.php';
                break;
            }
            error_log('Store error: ' . $e->getMessage());
            http_response_code(500);
            exit('Внутренняя ошибка сервера при сохранении.');
        }

    case 'edit':
        $item = $service->get($id);
        if ($item === null) {
            http_response_code(404);
            exit('Запись не найдена.');
        }
        $errors = [];
        $formAction = 'index.php?action=update&id=' . (int) $item['id'];
        $pageTitle = 'Редактировать объект';
        require __DIR__ . '/../templates/media/form.php';
        break;

    case 'update':
        verifyCsrf();
        $existing = $service->get($id);
        if ($existing === null) {
            http_response_code(404);
            exit('Запись не найдена.');
        }
        try {
            $result = $service->update($id, $_POST);
            if ($result['errors'] !== []) {
                http_response_code(422);
                $item = array_merge($existing, $_POST);
                $errors = $result['errors'];
                $formAction = 'index.php?action=update&id=' . $id;
                $pageTitle = 'Редактировать объект';
                require __DIR__ . '/../templates/media/form.php';
                break;
            }
            header('Location: index.php?action=show&id=' . $id, true, 303);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                $item = array_merge($existing, $_POST);
                $errors = ['catalog_no' => 'Этот каталожный номер уже занят другой записью.'];
                $formAction = 'index.php?action=update&id=' . $id;
                $pageTitle = 'Редактировать объект';
                require __DIR__ . '/../templates/media/form.php';
                break;
            }
            error_log('Update error: ' . $e->getMessage());
            http_response_code(500);
            exit('Внутренняя ошибка сервера при обновлении.');
        }

    case 'delete':
        verifyCsrf();
        if (!$service->delete($id)) {
            http_response_code(404);
            exit('Запись для удаления не найдена.');
        }
        header('Location: index.php?action=index', true, 303);
        exit;

    case 'issue':
        verifyCsrf();
        try {
            $service->issue($id);
            header('Location: index.php?action=show&id=' . $id, true, 303);
            exit;
        } catch (RuntimeException $e) {
            http_response_code(409);
            exit('Невозможно выдать объект: он не найден или уже выдан.');
        } catch (Throwable $e) {
            error_log('Transaction error: ' . $e->getMessage());
            http_response_code(500);
            exit('Ошибка выполнения операции выдачи. Изменения отменены.');
        }
}
