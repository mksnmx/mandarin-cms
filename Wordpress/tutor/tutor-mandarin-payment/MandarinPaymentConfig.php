<?php
namespace TutorMandarinPayment;

if (!class_exists('Ollyo\PaymentHub\Core\Payment\BaseConfig')) {
    return;
}

use Tutor\Ecommerce\Settings;
use Ollyo\PaymentHub\Core\Payment\BaseConfig;
use Tutor\PaymentGateways\Configs\PaymentUrlsTrait;
use Ollyo\PaymentHub\Contracts\Payment\ConfigContract;

class MandarinPaymentConfig extends BaseConfig implements ConfigContract {

    use PaymentUrlsTrait;

    private $environment = 'sandbox';
    private $merchant_id = '';
    private $secret_key = '';
    private $title = 'Mandarin';
    private $description = 'Оплата через Mandarin';
    protected $name = 'mandarin';

    public function __construct() {
        parent::__construct();

        // Загружаем настройки из базы данных
        $settings = Settings::get_payment_gateway_settings('mandarin');

        if (is_array($settings)) {
            $this->environment = $settings['environment'] ?? 'sandbox';
            $this->merchant_id = $settings['merchant_id'] ?? '';
            $this->secret_key = $settings['secret_key'] ?? '';
            $this->title = $settings['title'] ?? 'Mandarin';
            $this->description = $settings['description'] ?? 'Оплата через Mandarin';
        }
    }

    public function getMode(): string {
        return $this->environment;
    }

    public function getSecretKey(): string {
        return $this->secret_key;
    }

    public function getMerchantId(): string {
        return $this->merchant_id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function is_configured() {
        $configured = !empty($this->merchant_id) && !empty($this->secret_key);
        error_log('Tutor Mandarin: is_configured = ' . ($configured ? 'true' : 'false'));
        return $configured;
    }

    public function createConfig(): void {
        parent::createConfig();

        $this->updateConfig([
            'merchant_id' => $this->getMerchantId(),
            'secret_key' => $this->getSecretKey(),
            'mode' => $this->getMode(),
        ]);
    }
}