<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Метод не поддерживается.');
}
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Запрос отклонён: неверный CSRF-токен. Вернитесь назад и повторите действие.');
}
$idRaw = $_POST['book_id'] ?? '';
$id = is_string($idRaw) && ctype_digit($idRaw) ? (int)$idRaw : 0;
$books = loadBooks();
if (findBookById($books, $id) === null) {
    setFlash('Книга не найдена.', 'error');
} else {
    $ids = favoriteIds();
    if (in_array($id, $ids, true)) {
        $ids = array_values(array_filter($ids, static fn(int $favoriteId): bool => $favoriteId !== $id));
        setFlash('Книга удалена из избранного.');
    } else {
        $ids[] = $id;
        setFlash('Книга добавлена в избранное.');
    }
    $_SESSION['favorites'] = $ids;
}
$returnTo = $_POST['return_to'] ?? 'index.php';
if (!is_string($returnTo) || !preg_match('/^(index\.php|favorites\.php|book\.php\?id=[0-9]+)$/', $returnTo)) {
    $returnTo = 'index.php';
}
header('Location: ' . $returnTo, true, 303);
exit;
