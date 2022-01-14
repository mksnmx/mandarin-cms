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

if (empty($_POST['orderId']))
{
	Custom::inc('includes/404.php');
}

$pay = $this->diafan->_payment->check_pay($_POST['orderId'], 'mandarinpay');

if ($_POST['status'] == 'success') {
	$this->diafan->_payment->success($pay);
}
else {
	$this->diafan->_payment->fail($pay);
}