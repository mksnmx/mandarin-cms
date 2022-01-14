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

class Payment_mandarin_admin
{
	public $config;
	private $diafan;

	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->config = array(
			"name" => 'Mandarin',
			"params" => array(
				'mandarin_mid' => 'ID мерчанта',
                'mandarin_msec' => 'Secret мерчанта'
			)
		);
	}
}