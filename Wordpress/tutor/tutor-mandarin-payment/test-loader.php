<?php
// Подключаем WordPress
require_once '../../../wp-load.php';

// Подключаем наш плагин напрямую
require_once __DIR__ . '/tutor-mandarin-payment.php';

echo "<h2>Проверка загрузки классов Mandarin Payment:</h2>";
echo "<pre>";

// Проверяем наши классы
$our_classes = [
    'TutorMandarinPayment\\Init',
    'TutorMandarinPayment\\MandarinPaymentGateway',
    'TutorMandarinPayment\\MandarinPaymentConfig',
    'Payments\\Mandarin\\MandarinPayment'
];

foreach ($our_classes as $class) {
    $exists = class_exists($class);
    echo str_pad($class . ':', 50) . ($exists ? '✓ OK' : '✗ FAIL') . "\n";
}

echo "\n";
echo "Проверка базовых классов Tutor Pro:\n";

// Проверяем базовые классы Tutor Pro
$tutor_classes = [
    'Ollyo\\PaymentHub\\Core\\Payment\\BasePayment',
    'Ollyo\\PaymentHub\\Core\\Payment\\BaseConfig',
    'Tutor\\PaymentGateways\\GatewayBase',
    'Tutor\\Ecommerce\\Settings'
];

foreach ($tutor_classes as $class) {
    $exists = class_exists($class);
    echo str_pad($class . ':', 50) . ($exists ? '✓ OK' : '✗ FAIL') . "\n";
}

echo "\n";
echo "Проверка констант:\n";
echo "TUTOR_MANDARIN_PATH: " . (defined('TUTOR_MANDARIN_PATH') ? TUTOR_MANDARIN_PATH : 'NOT DEFINED') . "\n";
echo "TUTOR_MANDARIN_URL: " . (defined('TUTOR_MANDARIN_URL') ? TUTOR_MANDARIN_URL : 'NOT DEFINED') . "\n";

echo "</pre>";