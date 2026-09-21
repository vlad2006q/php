<?php
// === КОНСТАНТЫ ===
const UNIVERSITY = "Алматинский технологический университет";
const DISCIPLINE = "Программирование на PHP";
const MIN_SUM_FOR_BONUS = 5000; // Порог для успешного статуса

// === ПЕРЕМЕННЫЕ И ТИПЫ ДАННЫХ ===
$studentName = "Жолдас Қайсар"; // string
$group = "ИС-23-22";          // string
$course = 3;                   // int
$variant = 2;                  // int

// Обработка данных из формы (POST) или установка значений по умолчанию
$price = isset($_POST['price']) ? (float)$_POST['price'] : 1500.0;     // float (Цена)
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 4;   // int (Количество)
$discountPercent = isset($_POST['discountPercent']) ? (float)$_POST['discountPercent'] : 10.0; // float (Скидка в %)

// === ВЫЧИСЛЕНИЯ (Формула варианта №2) ===
// Формула: Цена * Количество * (1 - Скидка / 100)
$totalCost = $price * $quantity * (1 - $discountPercent / 100);
$totalCost1 = $price * $quantity;

// === УСЛОВНАЯ КОНСТРУКЦИЯ (if...else) ===
if ($totalCost >= MIN_SUM_FOR_BONUS) {
    $status = "Крупная покупка (получен повышенный бонусный кэшбэк)";
    $statusClass = "success";
} else {
    $status = "Обычная покупка (стандартный кэшбэк)";
    $statusClass = "warning";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная №1 — Вариант 2</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #f4f6f8;
        }
        .card {
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #2c3e50; font-size: 22px; margin-top: 0; }
        h2 { color: #34495e; font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result-box {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .success { color: green; font-weight: bold; }
        .warning { color: #d9534f; font-weight: bold; }
        .footer-info {
            font-size: 14px;
            color: #6c757d;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="card">
    <h1><?= UNIVERSITY ?></h1>
    <h2><?= DISCIPLINE ?> — Вариант №<?= $variant ?> (Стоимость покупки)</h2>
    
    <p><strong>Студент:</strong> <?= $studentName ?></p>
    <p><strong>Группа:</strong> <?= $group ?> | <strong>Курс:</strong> <?= $course ?></p>
</div>

<div class="card">
    <h2>Ввод исходных данных</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="price">Цена товара (₸):</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= $price ?>" required>
        </div>
        
        <div class="form-group">
            <label for="quantity">Количество (шт):</label>
            <input type="number" id="quantity" name="quantity" value="<?= $quantity ?>" min="1" required>
        </div>
        
        <div class="form-group">
            <label for="discountPercent">Скидка (%):</label>
            <input type="number" step="0.1" id="discountPercent" name="discountPercent" value="<?= $discountPercent ?>" min="0" max="100" required>
        </div>
        
        <button type="submit">Рассчитать стоимость</button>
    </form>
</div>

<div class="card">
    <h2>Результаты расчета</h2>
    
    <div class="result-box">
        <p><strong>Цена за единицу:</strong> <?= number_format($price, 2, '.', ' ') ?> ₸</p>
        <p><strong>Количество:</strong> <?= $quantity ?> шт.</p>
        <p><strong>Размер скидки:</strong> <?= $discountPercent ?>%</p>
        <hr>
        <p style="font-size: 18px;">
            <strong>Итоговая стоимость без скидки:</strong> 
            <span style="color: #007bff;"><?= number_format($totalCost1, 2, '.', ' ') ?> ₸</span>
        </p>
        <p style="font-size: 18px;">
            <strong>Итоговая стоимость с скидкой:</strong> 
            <span style="color: #007bff;"><?= number_format($totalCost, 2, '.', ' ') ?> ₸</span>
        </p>
    </div>

    <p><strong>Статус операции:</strong> <span class="<?= $statusClass ?>"><?= $status ?></span></p>
    <p class="footer-info"><strong>Дата формирования карточки:</strong> <?= date("d.m.Y H:i") ?></p>
</div>

</body>
</html>