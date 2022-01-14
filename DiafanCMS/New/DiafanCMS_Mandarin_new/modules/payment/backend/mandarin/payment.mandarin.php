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

$r = $_POST;
if(empty($r['orderId']))Custom::inc('includes/404.php');


function check_sign($secret, $req){
	$sign = $req['sign'];
	unset($req['sign']);
	$to_hash = '';
	if (!is_null($req) && is_array($req)){
		ksort($req);
		$to_hash = implode('-', $req);
	}
	$to_hash = $to_hash .'-'. $secret;
	$calculated_sign = hash('sha256', $to_hash);
	return $calculated_sign == $sign;
}

$pay = $this->diafan->_payment->check_pay($r['orderId'], 'mandarin');

if ($r['status'] == 'success' && check_sign($pay["params"]["mandarin_msec"],$r)) {
	if($this->diafan->_payment->success($pay)){
		die('OK');
	}
}
else {
	$this->diafan->_payment->fail($pay);
}