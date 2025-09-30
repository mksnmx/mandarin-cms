<?php
/**
 * Plugin Name:     Tutor Mandarin Payment
 * Plugin URI:      https://mandarin.io/
 * Description:     Интеграция платежной системы Mandarin для Tutor LMS
 * Author:          MandarinLtd
 * Author URI:      https://mandarin.io/
 * Text Domain:     tutor-mandarin-payment
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         TutorMandarinPayment
 */

if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы
define('TUTOR_MANDARIN_VERSION', '1.0.0');
define('TUTOR_MANDARIN_URL', plugin_dir_url(__FILE__));
define('TUTOR_MANDARIN_PATH', plugin_dir_path(__FILE__));
define('TUTOR_MANDARIN_PAYMENTS_DIR', trailingslashit(TUTOR_MANDARIN_PATH . 'src/Payments'));

// Автозагрузчик для всех классов
spl_autoload_register(function ($class) {
    // Карта namespace -> директория
    $namespaces = [
        'TutorMandarinPayment\\' => TUTOR_MANDARIN_PATH,
        'Payments\\Mandarin\\' => TUTOR_MANDARIN_PATH . 'src/Payments/Mandarin/',
//        'Ollyo\\PaymentHub\\' => TUTOR_MANDARIN_PATH . 'vendor/ollyo/payment-hub/src/',
//        'Tutor\\PaymentGateways\\Configs\\' => TUTOR_MANDARIN_PATH . 'vendor/tutor/PaymentGateways/Configs/',
    ];

    foreach ($namespaces as $namespace => $base_dir) {
        $len = strlen($namespace);
        if (strncmp($namespace, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

add_action('plugins_loaded', function() {
    if (is_plugin_active('tutor/tutor.php') && is_plugin_active('tutor-pro/tutor-pro.php')) {
        new TutorMandarinPayment\Init();
        error_log('Tutor Mandarin Payment: Init class instantiated');
    }
}, 20);