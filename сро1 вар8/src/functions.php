<?php
declare(strict_types=1);

const DATA_FILE = __DIR__ . '/../var/data/books.json';

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function loadBooks(): array
{
    if (!is_file(DATA_FILE)) {
        throw new RuntimeException('Файл каталога не найден. Проверьте var/data/books.json.');
    }
    $raw = file_get_contents(DATA_FILE);
    if ($raw === false) {
        throw new RuntimeException('Не удалось прочитать каталог книг.');
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException('Файл каталога содержит некорректный JSON.', 0, $exception);
    }
    if (!is_array($data)) {
        throw new RuntimeException('Формат каталога должен быть массивом JSON.');
    }
    return $data;
}

function findBookById(array $books, int $id): ?array
{
    foreach ($books as $book) {
        if ((int)($book['id'] ?? 0) === $id) {
            return $book;
        }
    }
    return null;
}

function lowerUtf8(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }
    return strtolower(strtr($value, [
        'А'=>'а','Б'=>'б','В'=>'в','Г'=>'г','Д'=>'д','Е'=>'е','Ё'=>'ё','Ж'=>'ж','З'=>'з','И'=>'и','Й'=>'й','К'=>'к','Л'=>'л','М'=>'м','Н'=>'н','О'=>'о','П'=>'п','Р'=>'р','С'=>'с','Т'=>'т','У'=>'у','Ф'=>'ф','Х'=>'х','Ц'=>'ц','Ч'=>'ч','Ш'=>'ш','Щ'=>'щ','Ъ'=>'ъ','Ы'=>'ы','Ь'=>'ь','Э'=>'э','Ю'=>'ю','Я'=>'я'
    ]));
}

function filterBooks(array $books, array $input): array
{
    $query = trim((string)($input['q'] ?? ''));
    $genre = trim((string)($input['genre'] ?? ''));
    $yearRaw = trim((string)($input['year'] ?? ''));
    $sort = (string)($input['sort'] ?? 'title_asc');
    $year = ($yearRaw !== '' && ctype_digit($yearRaw)) ? (int)$yearRaw : null;
    $needle = lowerUtf8($query);

    $result = array_values(array_filter($books, static function (array $book) use ($needle, $genre, $year): bool {
        $searchable = lowerUtf8((string)($book['title'] ?? '') . ' ' . (string)($book['author'] ?? '') . ' ' . (string)($book['description'] ?? ''));
        if ($needle !== '' && !str_contains($searchable, $needle)) {
            return false;
        }
        if ($genre !== '' && (string)($book['genre'] ?? '') !== $genre) {
            return false;
        }
        if ($year !== null && (int)($book['year'] ?? 0) !== $year) {
            return false;
        }
        return true;
    }));

    $comparators = [
        'title_asc' => static fn(array $a, array $b): int => strcasecmp((string)$a['title'], (string)$b['title']),
        'title_desc' => static fn(array $a, array $b): int => strcasecmp((string)$b['title'], (string)$a['title']),
        'year_desc' => static fn(array $a, array $b): int => ((int)$b['year']) <=> ((int)$a['year']),
        'year_asc' => static fn(array $a, array $b): int => ((int)$a['year']) <=> ((int)$b['year']),
    ];
    if (!isset($comparators[$sort])) {
        $sort = 'title_asc';
    }
    usort($result, $comparators[$sort]);
    return $result;
}

function uniqueGenres(array $books): array
{
    $genres = array_values(array_unique(array_map(static fn(array $book): string => (string)$book['genre'], $books)));
    sort($genres, SORT_NATURAL | SORT_FLAG_CASE);
    return $genres;
}

function uniqueYears(array $books): array
{
    $years = array_values(array_unique(array_map(static fn(array $book): int => (int)$book['year'], $books)));
    rsort($years, SORT_NUMERIC);
    return $years;
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(mixed $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $token);
}

function favoriteIds(): array
{
    $ids = $_SESSION['favorites'] ?? [];
    if (!is_array($ids)) {
        return [];
    }
    return array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
}

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type === 'error' ? 'error' : 'success'];
}

function takeFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}
