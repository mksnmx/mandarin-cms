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

class Payment_mandarinpay_model extends Diafan
{
	public function get($params, $pay)
	{
		$result["summ"]      = $pay["summ"];
		$result["order_id"]  = $pay["id"];
		$result["text"]      = $pay["text"];
		$result["desc"]      = $pay["desc"];
		$result["shopid"]    = $params["mandarinpay_MerchantId"];
		
		$result["cust_email"] = '';
		if(! empty($pay["details"]["email"]))
		{
			list($result["cust_email"], ) = explode(' ', $pay["details"]["email"]);
			$result["cust_email"] = str_replace('"', '&quot;', $result["cust_email"]);
		}
		
		//формируем подпись
		$tmp = $result["cust_email"] . '-' . $result["shopid"] . '-' . $result["order_id"] . '-' . $result["summ"] . '-' . $params["mandarinpay_secretKey"];
		$result['sign'] = hash("sha256", $tmp);
		
		return $result;
	}
}