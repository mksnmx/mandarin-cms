<?php

if (! defined('DIAFAN'))
{
	$path = __FILE__; $i = 0;
	while(! file_exists($path.'/includes/404.php'))
	{
		if($i == 10) exit; $i++;
		$path = dirname($path);
	}
	include $path.'/includes/404.php';
}

class Payment_mandarinpay_admin
{
	public $config;
	private $diafan;

	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->config = array(
			"name" => 'MadarinPay',
			"params" => array(
				'mandarinpay_MerchantId' => array(
                    'name' => 'Номер магазина в системе mandarinpay',
                    'help' => 'Номер магазина нужно скопировать из личного кабинета mandarinpay'
                ),
                'mandarinpay_secretKey' => array(
                    'name' => 'Секретный ключ в системе mandarinpay',
                    'help' => 'Укажите секретный ключ, такой же, который вы указали в личном кабинете mandarinpay'
                ),
			)
		);
	}
}