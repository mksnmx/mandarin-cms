<?php
class Mandarin_Blocks_Support extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    protected $name = 'mandarin_pay';

    public function initialize() {
        $this->settings = get_option('woocommerce_mandarin_pay_settings', []);
        error_log('Mandarin Blocks: initialized with settings: ' . print_r($this->settings, true));
    }

    public function is_active() {
        $is_active = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
        error_log('Mandarin Blocks: is_active = ' . ($is_active ? 'true' : 'false'));
        return $is_active;
    }

    public function get_payment_method_script_handles() {
        error_log('Mandarin Blocks: registering script handle');

        // Регистрируем наш JavaScript файл
        wp_register_script(
            'wc-mandarin-blocks',
            plugins_url('assets/js/mandarin-blocks.js', __FILE__),
            ['wc-blocks-registry', 'wp-element'], // Зависимости
            '1.0.0',
            true
        );

        return ['wc-mandarin-blocks'];
    }

    public function get_payment_method_data() {
        error_log('Mandarin Blocks: get_payment_method_data called');

        return [
            'title' => $this->get_setting('title', 'Mandarin'),
            'description' => $this->get_setting('description', 'Оплата через Mandarin'),
            'supports' => ['products']
        ];
    }

    protected function get_setting($key, $default = '') {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
}
