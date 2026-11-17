<?php
declare(strict_types=1);

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

try {
    $pdo = ConnectionFactory::create();
    echo "[OK] Подключение к БД успешно!\n";
} catch (Throwable $e) {
    echo "[ОШИБКА БД]: " . $e->getMessage() . "\n";
    exit(1);
}

$repo = new MediaRepository($pdo);
$service = new MediaService($repo);

// Очистка тестовых записей перед прогоном
$pdo->exec("DELETE FROM media WHERE catalog_no LIKE 'TST-%'");

// Тест 1: Корректное создание
$res1 = $service->create(['catalog_no' => 'TST-101', 'title' => 'Тестовая книга', 'type' => 'book', 'available' => '1']);
assert($res1['errors'] === [] && $res1['id'] > 0, 'Тест 1 провален');
$id = $res1['id'];
echo "[OK] Тест 1: Корректное создание (ID = $id)\n";

// Тест 2: Пустое поле (валидация до SQL)
$res2 = $service->create(['catalog_no' => 'TST-102', 'title' => '', 'type' => 'book', 'available' => '1']);
assert(isset($res2['errors']['title']), 'Тест 2 провален');
echo "[OK] Тест 2: Пустое название отклонено до SQL\n";

// Тест 3: Дубликат каталожного номера
$duplicateCaught = false;
try {
    $service->create(['catalog_no' => 'TST-101', 'title' => 'Дубликат', 'type' => 'video', 'available' => '1']);
} catch (PDOException $e) {
    $duplicateCaught = ($e->getCode() === '23000');
}
assert($duplicateCaught, 'Тест 3 провален');
echo "[OK] Тест 3: Дубликат catalog_no вызвал конфликт 23000\n";

// Тест 4: Чтение существующего ID
$found = $service->get($id);
assert($found !== null && $found['catalog_no'] === 'TST-101', 'Тест 4 провален');
echo "[OK] Тест 4: Чтение существующего ID\n";

// Тест 5: Неизвестный ID
assert($service->get(99999999) === null, 'Тест 5 провален');
echo "[OK] Тест 5: Неизвестный ID вернул null (404)\n";

// Тест 6: Изменение значений
$upd1 = $service->update($id, ['catalog_no' => 'TST-101', 'title' => 'Обновлённая книга', 'type' => 'audio', 'available' => '1']);
assert($upd1['updated'] === true && $service->get($id)['title'] === 'Обновлённая книга', 'Тест 6 провален');
echo "[OK] Тест 6: Изменение значений сохранено\n";

// Тест 7: Сохранение прежних значений (без ложной ошибки rowCount=0)
$upd2 = $service->update($id, ['catalog_no' => 'TST-101', 'title' => 'Обновлённая книга', 'type' => 'audio', 'available' => '1']);
assert($upd2['updated'] === true, 'Тест 7 провален');
echo "[OK] Тест 7: Прежние значения без ложной ошибки rowCount\n";

// Тест 8: SQL-фрагмент в поиске обработан как текст
$sqlSearch = $service->list("' OR 1=1 --");
assert($sqlSearch === [], 'Тест 8 провален');
echo "[OK] Тест 8: SQL-инъекция в поиске безопасна\n";

// Тест 9: Неверная сортировка заменяется полем по умолчанию
$sortList = $service->list('TST-', 'DROP TABLE media; --');
assert(count($sortList) === 1, 'Тест 9 провален');
echo "[OK] Тест 9: Недопустимая сортировка отфильтрована allowlist\n";

// Тест 10: Транзакционная выдача и аудит (commit)
$service->issue($id);
assert((int) $service->get($id)['available'] === 0, 'Тест 10 провален');
echo "[OK] Тест 10: Транзакция issueWithAudit выполнена (available = 0)\n";

// Тест 11: Откат транзакции (rollback) при ошибке
$pdo->beginTransaction();
$pdo->exec("UPDATE media SET available = 1 WHERE id = $id");
$pdo->rollBack();
assert((int) $service->get($id)['available'] === 0, 'Тест 11 провален');
echo "[OK] Тест 11: Проверка rollback подтверждена\n";

// Тест 12: Удаление записи
assert($service->delete($id) === true && $service->get($id) === null, 'Тест 12 провален');
echo "[OK] Тест 12: Удаление записи прошло успешно\n";

echo "\nВсе 12 тестов успешно пройдены!\n";
