<?php
class Mandarin_Blocks_Support_v3 extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    protected $name = 'mandarin_pay_v3';

    public function initialize() {
        $this->settings = get_option('woocommerce_mandarin_pay_v3_settings', []);
    }

    public function is_active() {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    public function get_payment_method_script_handles() {
        wp_register_script(
            'wc-mandarin-blocks-v3',
            plugins_url('assets/js/mandarin-blocks.js', __FILE__),
            ['wc-blocks-registry', 'wp-element'],
            '3.0.0',
            true
        );

        return ['wc-mandarin-blocks-v3'];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->get_setting('title', 'Mandarin v3'),
            'description' => $this->get_setting('description', 'Оплата через Mandarin'),
            'supports' => ['products']
        ];
    }
}
