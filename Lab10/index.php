<?php
declare(strict_types=1);

require_once __DIR__ . '/core.php';

use App\Delivery\CourierCalculator;
use App\Delivery\PickupCalculator;
use App\Services\DeliveryService as CostEvaluator;

$service = new CostEvaluator([
    new CourierCalculator(),
    new PickupCalculator()
]);

// Если это AJAX-запрос, отдаем JSON и завершаем скрипт
if (isset($_GET['action']) && $_GET['action'] === 'calculate') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($service->calculateDynamic($_POST));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Интерактивная служба доставки (Вариант 4)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 15px; }
        button { background: #3182ce; color: #fff; border: none; padding: 12px 20px; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.2s; }
        button:hover { background: #2b6cb0; }
        .result-box { display: none; padding: 15px; border-radius: 4px; margin-top: 15px; font-weight: 500; }
        .success { background: #c6f6d5; color: #22543d; border: 1px solid #98e6b1; }
        .error { background: #fed7d7; color: #742a2a; border: 1px solid #feb2b2; }
        .log-box { background: #1a202c; color: #a0aec0; font-family: Courier, monospace; padding: 15px; border-radius: 4px; height: 250px; overflow-y: auto; white-space: pre-wrap; font-size: 13px; }
        .info-pill { background: #e2e8f0; padding: 8px; border-radius: 4px; font-size: 12px; margin-top: 10px; color: #4a5568; line-height: 1.4; }
    </style>
</head>
<body>

<div style="text-align: center; margin-bottom: 30px;">
    <h1 style="color: #2d3748; margin-bottom: 5px;">Служба расчёта доставки</h1>
    <small style="color: #718096; font-size: 14px;">Университетская ИС — Лабораторная работа 10 (PHP 8.4+)</small>
</div>

<div class="container">
    <!-- Левая панель: Форма параметров -->
    <div class="card">
        <h2>Параметры отправления</h2>
        <form id="deliveryForm">
            <div class="form-group">
                <label for="type">Тип доставки (Канал)</label>
                <select id="type" name="type">
                    <option value="courier">Курьерская доставка (CourierCalculator)</option>
                    <option value="pickup">Самовывоз из пункта (PickupCalculator)</option>
                    <option value="drone">Дроны (Неизвестный канал — тест ошибки)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="address">Адрес назначения / Код ПВЗ</label>
                <input type="text" id="address" name="address" value="г. Алматы, ул. Абая 45" placeholder="Например: ул. Мира 12">
            </div>

            <div class="form-group">
                <label for="weight">Вес посылки (кг)</label>
                <input type="number" id="weight" name="weight" value="5" step="0.1">
            </div>

            <div class="form-group">
                <label for="value">Объявленная ценность (₸)</label>
                <input type="number" id="value" name="value" value="12000">
            </div>

            <button type="submit">Рассчитать стоимость</button>
        </form>

        <div id="resultBox" class="result-box"></div>
    </div>

    <!-- Правая панель: Диагностика ООП и Логи -->
    <div class="card">
        <h2>Диагностический Trait логов</h2>
        <div id="logConsole" class="log-box">Система готова. Ожидание расчёта...</div>
        
        <div class="info-pill">
            <strong>Правила триггеров ошибок для защиты работы:</strong><br>
            • Курьер: Адрес со словом "деревня" вызовет <code>AddressException</code>.<br>
            • Курьер: Вес > 30 кг вызовет системный сбой (проверка сохранения <code>previous</code>).<br>
            • ПВЗ: Адрес, состоящий только из цифр, вызовет <code>AddressException</code>.
        </div>
    </div>
</div>

<script>
document.getElementById('deliveryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultBox = document.getElementById('resultBox');
    const logConsole = document.getElementById('logConsole');
    
    resultBox.style.display = 'none';
    
    try {
        const response = await fetch('index.php?action=calculate', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        // Вывод логов из Трейта
        if(data.logs && data.logs.length > 0) {
            logConsole.innerHTML = data.logs.map(log => `<span style="color: #48bb78;">[LOG]</span> ${log}`).join('\n');
        } else {
            logConsole.innerHTML = '<span style="color: #ed8936;">[SYSTEM]</span> Канал не принял запрос (Общий сбой).';
        }
        
        // Вывод результата
        if (data.success) {
            resultBox.className = 'result-box success';
            resultBox.innerHTML = `<strong>Успех!</strong><br>Стоимость: ${data.price} ₸<br>Срок: ${data.days} дн. (Канал: ${data.type})`;
        } else {
            resultBox.className = 'result-box error';
            resultBox.innerHTML = `<strong>Ошибка!</strong><br>${data.error}`;
            logConsole.innerHTML += `\n<span style="color: #f56565;">[ERROR]</span> ${data.error}`;
        }
        
    } catch (err) {
        resultBox.className = 'result-box error';
        resultBox.innerHTML = 'Произошла критическая ошибка выполнения AJAX.';
    }
    
    resultBox.style.display = 'block';
});
</script>

</body>
</html>
