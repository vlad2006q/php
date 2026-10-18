<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
$books = loadBooks();
$idRaw = $_GET['id'] ?? '';
$id = is_string($idRaw) && ctype_digit($idRaw) ? (int)$idRaw : 0;
$book = findBookById($books, $id);
if ($book === null) { http_response_code(404); }
$flash = takeFlash();
$favorites = favoriteIds();
?><!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $book ? e($book['title']) . ' — БиблиоКаталог' : 'Книга не найдена' ?></title><link rel="stylesheet" href="assets/style.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="index.php">📚 БиблиоКаталог</a><nav><a class="nav-link" href="index.php">Каталог</a><a class="nav-link" href="favorites.php">Избранное <span class="count"><?= count($favorites) ?></span></a></nav></div></header><main class="container page-content">
<?php if ($flash !== null): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($book === null): ?><div class="empty-state"><h1>Книга не найдена</h1><p>Возможно, ссылка устарела.</p><a class="button" href="index.php">Вернуться в каталог</a></div><?php else: ?>
<a class="back-link" href="index.php">← Вернуться в каталог</a><article class="detail-card"><div class="detail-cover cover-<?= (int)$book['id'] % 6 ?>"><span class="cover-icon">▤</span><span class="cover-title"><?= e($book['title']) ?></span><span class="cover-author"><?= e($book['author']) ?></span></div><div class="detail-info"><span class="genre-tag"><?= e($book['genre']) ?></span><h1><?= e($book['title']) ?></h1><p class="detail-author">Автор: <?= e($book['author']) ?></p><div class="detail-facts"><div><small>Год издания</small><strong><?= e($book['year']) ?></strong></div><div><small>Жанр</small><strong><?= e($book['genre']) ?></strong></div><div><small>Номер в каталоге</small><strong>#<?= (int)$book['id'] ?></strong></div></div><h2>О книге</h2><p class="detail-description"><?= e($book['description']) ?></p><form method="post" action="favorite.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><input type="hidden" name="return_to" value="book.php?id=<?= (int)$book['id'] ?>"><button class="button" type="submit"><?= in_array((int)$book['id'], $favorites, true) ? 'Убрать из избранного' : '♡ Добавить в избранное' ?></button></form></div></article><?php endif; ?></main><footer class="site-footer"><div class="container">Учебный проект на PHP 8.4</div></footer></body></html>
