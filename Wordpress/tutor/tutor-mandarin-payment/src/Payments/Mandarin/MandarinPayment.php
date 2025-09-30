<?php
/**
 * Mandarin Payment Processing Class
 *
 * @package TutorMandarinPayment
 * @author MandarinLtd
 * @link https://mandarin.io
 * @since 1.0.0
 */

namespace Payments\Mandarin;

if (!class_exists('Ollyo\PaymentHub\Core\Payment\BasePayment')) {
    return;
}

use Throwable;
use ErrorException;
use Ollyo\PaymentHub\Core\Support\Arr;
use Ollyo\PaymentHub\Core\Support\System;
use GuzzleHttp\Exception\RequestException;
use Ollyo\PaymentHub\Core\Payment\BasePayment;

/**
 * MandarinPayment class
 *
 * Основной класс обработки платежей через Mandarin.
 * Адаптирован из WooCommerce версии для работы с Tutor LMS.
 */
class MandarinPayment extends BasePayment
{
    /**
     * URL платежного шлюза Mandarin
     */
    const PAYMENT_URL = 'https://secure.mandarinpay.com/Pay';

    /**
     * Проверка наличия всех необходимых настроек
     *
     * Убеждается, что все обязательные параметры конфигурации присутствуют
     * перед началом обработки платежа.
     *
     * @return bool True если все параметры есть, иначе false
     */
    public function check(): bool
    {
        $configKeys = Arr::make(['merchant_id', 'secret_key']);

        $isConfigOk = $configKeys->every(function($key) {
            return $this->config->has($key) && !empty($this->config->get($key));
        });

        return $isConfigOk;
    }

    /**
     * Инициализация платежного шлюза
     *
     * Подготавливает необходимые параметры для работы с Mandarin.
     * В отличие от некоторых других шлюзов, Mandarin не требует
     * предварительной инициализации клиента.
     */
    public function setup(): void
    {
        try {
            // Логирование для отладки (опционально)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Mandarin Payment Gateway initialized');
            }
        } catch (Throwable $error) {
            throw $error;
        }
    }

    /**
     * Установка данных платежа
     *
     * Принимает данные от Tutor LMS и структурирует их для Mandarin.
     *
     * @param  object $data Данные платежа от Tutor
     * @throws Throwable
     */
    public function setData($data): void
    {
        try {
            // Структурируем данные согласно требованиям Mandarin
            $structuredData = $this->prepareData($data);

            // Передаем структурированные данные родительскому классу
            parent::setData($structuredData);

        } catch (Throwable $error) {
            throw $error;
        }
    }

    /**
     * Подготовка данных для Mandarin
     *
     * Преобразует данные из формата Tutor в формат, понятный Mandarin.
     *
     * @param object $data Исходные данные от Tutor
     * @return object Подготовленные данные
     */
    private function prepareData($data)
    {
        // Адаптируем данные из Tutor к формату Mandarin
        // Tutor передает: order_id, amount, user_id, course_id и т.д.

        // Получаем информацию о пользователе
        $user = get_userdata($data->user_id ?? 0);

        // Формируем структурированные данные
        $prepared = (object) [
            'order_id' => $data->order_id ?? uniqid('tutor_'),
            'amount' => $data->amount ?? 0,
            'customer_email' => $user ? $user->user_email : '',
            'customer_phone' => get_user_meta($data->user_id, 'phone', true) ?: '',
            'course_id' => $data->course_id ?? 0,
            'course_title' => get_the_title($data->course_id ?? 0),
            'user_id' => $data->user_id ?? 0,
            'custom_fields' => [
                'platform' => 'Tutor LMS',
                'course_id' => $data->course_id ?? 0,
            ]
        ];

        return $prepared;
    }

    /**
     * Создание платежа
     *
     * Генерирует форму для редиректа на страницу оплаты Mandarin.
     * Адаптировано из process_payment и generate_form в WooCommerce версии.
     */
    public function createPayment()
    {
        try {
            // Получаем параметры конфигурации
            $merchant_id = $this->config->get('merchant_id');
            $secret_key = $this->config->get('secret_key');
            $success_url = $this->config->get('success_url');
            $cancel_url = $this->config->get('cancel_url');
            $webhook_url = $this->config->get('webhook_url');

            // Получаем данные платежа
            $payment_data = $this->getData();

            // Подготавливаем поля для отправки в Mandarin
            $fields = array(
                'merchantId' => $merchant_id,
                'orderId' => $payment_data->order_id,
                'price' => number_format($payment_data->amount, 2, '.', ''),
                'customer_email' => $payment_data->customer_email,
                'callbackUrl' => $webhook_url,
                'returnUrl' => $success_url,
            );

            // Добавляем телефон, если есть
            if (!empty($payment_data->customer_phone)) {
                $fields['customer_phone'] = $payment_data->customer_phone;
            }

            // Добавляем кастомные поля с информацией о курсе
            if (!empty($payment_data->custom_fields)) {
                $fields['customValues'] = json_encode($payment_data->custom_fields);
            }

            // Генерация подписи (аналогично calc_sign из WooCommerce)
            $sign = $this->calculateSignature($fields, $secret_key);
            $fields['sign'] = $sign;

            // Генерация HTML формы с автоматической отправкой
            $this->outputPaymentForm($fields);

        } catch (RequestException $error) {
            throw new ErrorException($error->getResponse()->getBody());
        }
    }

    /**
     * Расчет подписи для запроса
     *
     * Использует алгоритм подписи Mandarin для обеспечения безопасности.
     *
     * @param array $fields Поля для подписи
     * @param string $secret Секретный ключ
     * @return string Хеш подписи
     */
    private function calculateSignature($fields, $secret)
    {
        // Сортируем поля по ключу
        ksort($fields);

        // Собираем строку для подписи
        $values = array_values($fields);
        $sign_string = implode('-', $values) . '-' . $secret;

        // Возвращаем SHA256 хеш
        return hash('sha256', $sign_string);
    }

    /**
     * Вывод формы для редиректа на платежную страницу
     *
     * Создает красивую страницу загрузки с автоматическим редиректом.
     *
     * @param array $fields Поля формы
     */
    private function outputPaymentForm($fields)
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php _e('Перенаправление на страницу оплаты...', 'tutor-mandarin-payment'); ?></title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background: #f5f7fa;
                }
                .payment-redirect {
                    text-align: center;
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    max-width: 400px;
                }
                .spinner {
                    width: 50px;
                    height: 50px;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #3498db;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 20px;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                h2 {
                    color: #333;
                    margin: 0 0 10px;
                    font-size: 24px;
                }
                p {
                    color: #666;
                    margin: 0 0 20px;
                }
                .button {
                    background: #3498db;
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                    display: none;
                    text-decoration: none;
                    display: inline-block;
                }
                .button:hover {
                    background: #2980b9;
                }
                #manual-submit {
                    display: none;
                }
            </style>
        </head>
        <body>
        <div class="payment-redirect">
            <div class="spinner" id="spinner"></div>
            <h2><?php _e('Подготовка к оплате...', 'tutor-mandarin-payment'); ?></h2>
            <p id="message"><?php _e('Пожалуйста, подождите, мы перенаправляем вас на безопасную страницу оплаты.', 'tutor-mandarin-payment'); ?></p>

            <!-- Форма для отправки -->
            <form id="mandarin_form" action="<?php echo self::PAYMENT_URL; ?>" method="POST">
                <?php foreach($fields as $key => $value): ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endforeach; ?>
            </form>

            <!-- Кнопка для ручной отправки -->
            <button id="manual-submit" class="button" onclick="document.getElementById('mandarin_form').submit();">
                <?php _e('Перейти к оплате', 'tutor-mandarin-payment'); ?>
            </button>
        </div>

        <script>
            // Автоматическая отправка формы через 2 секунды
            setTimeout(function() {
                console.log('Отправка формы оплаты Mandarin...');
                document.getElementById('mandarin_form').submit();
            }, 2000);

            // Показываем кнопку ручной отправки через 5 секунд
            setTimeout(function() {
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('message').innerHTML = '<?php _e('Если перенаправление не произошло автоматически, нажмите кнопку ниже.', 'tutor-mandarin-payment'); ?>';
                document.getElementById('manual-submit').style.display = 'inline-block';
            }, 5000);
        </script>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Верификация и обработка данных от платежного шлюза
     *
     * Обрабатывает webhook (callback) от Mandarin после оплаты.
     * Адаптировано из check_ipn_response в WooCommerce версии.
     *
     * @param  object $payload Данные запроса (GET, POST, SERVER, php://input)
     * @return object Структурированные данные о результате платежа
     * @throws Throwable
     */
    public function verifyAndCreateOrderData(object $payload): object
    {
        // Получаем данные из различных источников
        $get_data = $payload->get;
        $post_data = $payload->post;
        $server_variables = $payload->server;
        $stream = $payload->stream;

        // Инициализируем объект с данными по умолчанию
        $returnData = System::defaultOrderData();

        try {
            // Проверяем, что это callback от Mandarin
            if (!isset($post_data['status'])) {
                throw new ErrorException('Invalid callback: missing status field');
            }

            // Обработка успешного платежа
            if ($post_data['status'] === 'success') {
                // Собираем данные для проверки подписи
                $hash_arr = array();
                foreach($post_data as $key => $value) {
                    if ($key !== 'sign') {
                        // Санитизация данных в зависимости от типа
                        switch ($key) {
                            case 'email':
                            case 'customer_email':
                                $hash_arr[$key] = sanitize_email($value);
                                break;
                            case 'orderId':
                            case 'merchantId':
                                $hash_arr[$key] = sanitize_key($value);
                                break;
                            default:
                                $hash_arr[$key] = sanitize_text_field($value);
                        }
                    }
                }

                // Проверяем подпись
                ksort($hash_arr);
                $calculated_hash = hash('sha256', implode('-', $hash_arr) . '-' . $this->config->get('secret_key'));

                if ($calculated_hash === $post_data['sign']) {
                    // Подпись верна, платеж успешен
                    $returnData->id = $post_data['orderId'];
                    $returnData->payment_status = 'completed';
                    $returnData->payment_error_reason = '';
                    $returnData->transaction_id = $post_data['transaction'] ?? $post_data['orderId'];
                    $returnData->payment_payload = json_encode($post_data);

                    // Рассчитываем комиссии и доходы, если есть информация
                    if (isset($post_data['amount'])) {
                        $amount = floatval($post_data['amount']);
                        // Примерный расчет комиссии (2.5% + 5 руб)
                        $returnData->fees = number_format(($amount * 0.025) + 5, 2, '.', '');
                        $returnData->earnings = number_format($amount - $returnData->fees, 2, '.', '');
                    }

                    // Отправляем подтверждение Mandarin
                    echo 'ok';

                } else {
                    // Неверная подпись
                    $returnData->payment_status = 'failed';
                    $returnData->payment_error_reason = 'Invalid signature';

                    echo 'error: Invalid signature';
                }

            } else {
                // Платеж не успешен
                $returnData->id = $post_data['orderId'] ?? '';
                $returnData->payment_status = 'failed';
                $returnData->payment_error_reason = $post_data['error'] ?? 'Payment failed';
                $returnData->payment_payload = json_encode($post_data);
            }

            return $returnData;

        } catch (Throwable $error) {
            // Логируем ошибку
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Mandarin callback error: ' . $error->getMessage());
            }

            $returnData->payment_status = 'failed';
            $returnData->payment_error_reason = $error->getMessage();

            throw $error;
        }
    }
}
