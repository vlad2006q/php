<?php
declare(strict_types=1);

namespace App\Validation;

final class MediaValidator
{
    public const ALLOWED_TYPES = ['book', 'video', 'audio', 'magazine'];

    public static function validate(array $input): array
    {
        $catalogNo = strtoupper(trim((string) ($input['catalog_no'] ?? '')));
        $title = trim((string) ($input['title'] ?? ''));
        $type = trim((string) ($input['type'] ?? ''));
        $available = isset($input['available']) && (string) $input['available'] === '1' ? 1 : 0;

        $errors = [];

        if (!preg_match('/^[A-Z]{2,10}-[0-9]{3}$/', $catalogNo)) {
            $errors['catalog_no'] = 'Формат каталожного номера: MED-101 (2–10 заглавных латинских букв, дефис и 3 цифры).';
        }

        if ($title === '' || mb_strlen($title) > 180) {
            $errors['title'] = 'Название обязательно и не должно превышать 180 символов.';
        }

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $errors['type'] = 'Выберите допустимый тип: book, video, audio или magazine.';
        }

        return [
            'data' => [
                'catalog_no' => $catalogNo,
                'title' => $title,
                'type' => $type,
                'available' => $available,
            ],
            'errors' => $errors,
        ];
    }
}
