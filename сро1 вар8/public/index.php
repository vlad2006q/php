<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

try {
    $allBooks = loadBooks();
    $books = filterBooks($allBooks, $_GET);
    $genres = uniqueGenres($allBooks);
    $years = uniqueYears($allBooks);
} catch (RuntimeException $exception) {
    http_response_code(500);
    $pageError = $exception->getMessage();
    $allBooks = $books = $genres = $years = [];
}
$flash = takeFlash();
$favorites = favoriteIds();
?><!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>БиблиоКаталог — электронная библиотека</title><link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="index.php">📚 БиблиоКаталог</a><nav><a class="nav-link active" href="index.php">Каталог</a><a class="nav-link" href="favorites.php">Избранное <span class="count"><?= count($favorites) ?></span></a></nav></div></header>
<main class="container">
  <section class="hero"><div><p class="eyebrow">ЭЛЕКТРОННАЯ БИБЛИОТЕКА</p><h1>Найдите следующую<br>любимую книгу</h1><p class="hero-text">Поиск по каталогу, фильтры и личный список избранного.</p></div><div class="hero-art" aria-hidden="true">📖</div></section>
  <?php if (isset($pageError)): ?><div class="alert error"><?= e($pageError) ?></div><?php else: ?>
  <?php if ($flash !== null): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
  <section class="filter-panel"><form method="get" action="index.php" class="filters">
    <label class="search-label">Поиск по названию, автору или описанию<input type="search" name="q" value="<?= e((string)($_GET['q'] ?? '')) ?>" placeholder="Например, Булгаков"></label>
    <label>Жанр<select name="genre"><option value="">Все жанры</option><?php foreach ($genres as $genre): ?><option value="<?= e($genre) ?>" <?= (($_GET['genre'] ?? '') === $genre) ? 'selected' : '' ?>><?= e($genre) ?></option><?php endforeach; ?></select></label>
    <label>Год<select name="year"><option value="">Любой год</option><?php foreach ($years as $year): ?><option value="<?= e($year) ?>" <?= ((string)($_GET['year'] ?? '') === (string)$year) ? 'selected' : '' ?>><?= e($year) ?></option><?php endforeach; ?></select></label>
    <label>Сортировка<select name="sort"><option value="title_asc" <?= (($_GET['sort'] ?? 'title_asc') === 'title_asc') ? 'selected' : '' ?>>Название А—Я</option><option value="title_desc" <?= (($_GET['sort'] ?? '') === 'title_desc') ? 'selected' : '' ?>>Название Я—А</option><option value="year_desc" <?= (($_GET['sort'] ?? '') === 'year_desc') ? 'selected' : '' ?>>Сначала новые</option><option value="year_asc" <?= (($_GET['sort'] ?? '') === 'year_asc') ? 'selected' : '' ?>>Сначала старые</option></select></label>
    <button class="button" type="submit">Применить</button><a class="button button-light" href="index.php">Сбросить</a>
  </form></section>
  <div class="section-heading"><div><h2>Каталог книг</h2><p>Найдено книг: <?= count($books) ?></p></div></div>
  <?php if ($books === []): ?><div class="empty-state"><span>🔎</span><h3>Ничего не найдено</h3><p>Попробуйте изменить запрос или сбросить фильтры.</p><a class="button" href="index.php">Показать все книги</a></div><?php else: ?>
  <section class="book-grid"><?php foreach ($books as $book): ?><article class="book-card"><div class="book-cover cover-<?= (int)$book['id'] % 6 ?>"><span class="cover-icon">▤</span><span class="cover-title"><?= e($book['title']) ?></span><span class="cover-author"><?= e($book['author']) ?></span></div><div class="book-info"><div class="book-meta"><span class="genre-tag"><?= e($book['genre']) ?></span><span><?= e($book['year']) ?></span></div><h3><a href="book.php?id=<?= (int)$book['id'] ?>"><?= e($book['title']) ?></a></h3><p class="author"><?= e($book['author']) ?></p><p class="description"><?= e($book['description']) ?></p><div class="card-bottom"><a class="text-link" href="book.php?id=<?= (int)$book['id'] ?>">Подробнее →</a><form method="post" action="favorite.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>"><input type="hidden" name="return_to" value="index.php"><button class="favorite-button <?= in_array((int)$book['id'], $favorites, true) ? 'is-favorite' : '' ?>" type="submit" aria-label="Добавить в избранное"><?= in_array((int)$book['id'], $favorites, true) ? '♥' : '♡' ?></button></form></div></div></article><?php endforeach; ?></section>
  <?php endif; ?><?php endif; ?>
</main><footer class="site-footer"><div class="container">Учебный проект на PHP 8.4 · Данные каталога хранятся в JSON</div></footer>
</body></html>
