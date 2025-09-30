<?php
namespace TutorMandarinPayment;

// Правильный namespace для GatewayBase
use Tutor\PaymentGateways\GatewayBase;
use Payments\Mandarin\MandarinPayment;

class MandarinPaymentGateway extends GatewayBase {

    private $dir_name = 'Mandarin';
    private $config_class = MandarinPaymentConfig::class;
    private $payment_class = MandarinPayment::class;

    public function get_root_dir_name(): string {
        return $this->dir_name;
    }

    public function get_payment_class(): string {
        return $this->payment_class;
    }

    public function get_config_class(): string {
        return $this->config_class;
    }

    public static function get_autoload_file() {
        return '';
    }
}