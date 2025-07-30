<?php
class Mandarin_Blocks_Support extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    protected $name = 'mandarin_pay';

    public function initialize() {
        $this->settings = get_option('woocommerce_mandarin_pay_settings', []);
    }

    public function is_active() {
        return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
    }

    public function get_payment_method_script_handles() {
        return [];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
        ];
    }
}
/**
 * Class Mandarin_Blocks_Support
 *
 * This class integrates the Mandarin payment method with WooCommerce Blocks.
 */