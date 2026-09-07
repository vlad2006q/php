<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Константы
const UNIVERSITY = "Алматинский технологический университет";
const DISCIPLINE = "Программирование на PHP";
const PASSING_SCORE = 50;

// Данные студента
$studentName = "Apetayeva Talshyn";
$group = "ИС-24-22";
$course = 3;
$variantNumber = 1;

// Исходные данные (Вариант 1)
$grade1 = 85;
$grade2 = 90;
$grade3 = 78;

// Вычисления
$averageGrade = ($grade1 + $grade2 + $grade3) / 3;

// Проверка условия
if ($averageGrade >= PASSING_SCORE) {
    $status = "Дисциплина освоена";
    $statusClass = "success";
} else {
    $status = "Необходимо повысить результат";
    $statusClass = "warning";
}

$currentDate = date("d.m.Y");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №1</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background: #f4f6f8; }
        .card { padding: 25px; background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .success { color: green; font-weight: bold; }
        .warning { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1><?php echo UNIVERSITY; ?></h1>
        <h2>Дисциплина: <?php echo DISCIPLINE; ?></h2>
        
        <div class="section-title">Сведения о студенте</div>
        <p>Студент: <?php echo $studentName; ?></p>
        <p>Группа: <?php echo $group; ?> | Курс: <?php echo $course; ?> | Вариант: №<?php echo $variantNumber; ?></p>

        <div class="section-title">Исходные данные</div>
        <p>Оценка 1: <?php echo $grade1; ?></p>
        <p>Оценка 2: <?php echo $grade2; ?></p>
        <p>Оценка 3: <?php echo $grade3; ?></p>

        <div class="section-title">Результаты</div>
        <p>Средний балл: <?php echo round($averageGrade, 2); ?></p>
        <p>Статус: <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span></p>
        <p>Дата формирования: <?php echo $currentDate; ?></p>
    </div>
</body>
</html>