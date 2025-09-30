<?php
namespace TutorMandarinPayment;

final class Init
{
    public function __construct()
    {
        add_filter('tutor_payment_gateways', array($this, 'add_tutor_mandarin_payment_method'), 10);
        add_filter('tutor_payment_gateways_with_class', array($this, 'register_gateway_classes'), 10);
        add_filter('tutor_gateways_with_class', array($this, 'register_webhook_classes'), 10, 2);
        error_log('Tutor Mandarin: Filters attached? tutor_payment_gateways: ' . (has_filter('tutor_payment_gateways') ? 'yes' : 'no'));
        error_log('Tutor Mandarin: Filters attached? tutor_payment_gateways_with_class: ' . (has_filter('tutor_payment_gateways_with_class') ? 'yes' : 'no'));
    }

    public function register_gateway_classes($gateways)
    {
        error_log('Tutor Mandarin: Filter hit - ' . __METHOD__ . ' called with args: ' . print_r(func_get_args(), true));
        if (!isset($gateways['mandarin'])) {
            $gateways['mandarin'] = array(
                'gateway_class' => 'TutorMandarinPayment\MandarinPaymentGateway',
                'config_class' => 'TutorMandarinPayment\MandarinPaymentConfig',
            );
        }

        return $gateways;
    }

    public function register_webhook_classes($value, $gateway)
    {
        error_log('Tutor Mandarin Payment: Registering classes for ' . print_r($gateway, true));
        if ($gateway === 'mandarin') {
            $value['mandarin'] = array(
                'gateway_class' => 'TutorMandarinPayment\MandarinPaymentGateway',
                'config_class' => 'TutorMandarinPayment\MandarinPaymentConfig',
            );
        }

        return $value;
    }

    public function add_tutor_mandarin_payment_method($methods)
    {
//        error_log('Tutor Mandarin: Filter hit - ' . __METHOD__ . ' called with args: ' . print_r(func_get_args(), true));
        $mandarin_payment_method = array(
            'name' => 'mandarin',
            'label' => 'Mandarin',
            'slug' => 'mandarin',
            'is_installed' => true,
            'is_active' => true,
            'enabled' => true,
            'is_plugin_active' => true,
            'icon' => TUTOR_MANDARIN_URL . 'assets/mandarin-icon.svg',
            'support_subscription' => false,
            'is_manual' => false,
            'update_available' => false,
            'fields' => array(
                array(
                    'name' => 'environment',
                    'type' => 'select',
                    'label' => 'Режим работы',
                    'options' => array(
                        'sandbox' => 'Тестовый',
                        'live' => 'Боевой',
                    ),
                    'value' => 'sandbox',
                ),
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => 'Заголовок',
                    'value' => 'Mandarin',
                ),
                array(
                    'name' => 'description',
                    'type' => 'textarea',
                    'label' => 'Описание',
                    'value' => 'Оплата банковскими картами через Mandarin',
                ),
                array(
                    'name' => 'merchant_id',
                    'type' => 'text',
                    'label' => 'Merchant ID',
                    'value' => '',
                ),
                array(
                    'name' => 'secret_key',
                    'type' => 'secret_key',
                    'label' => 'Secret Key',
                    'value' => '',
                ),
                array(
                    'name' => 'webhook_url',
                    'type' => 'webhook_url',
                    'label' => 'Webhook URL',
                    'value' => '',
                ),
            ),
        );

        if (!is_array($methods)) $methods = [];

        $methods[] = $mandarin_payment_method;
        return $methods;
    }
}