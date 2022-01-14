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

class Payment_mandarin_model extends Diafan
{
	function calc_sign($secret, $fields)
	{
		ksort($fields);
		$secret_t = '';
		foreach($fields as $key => $val)
		{
			$secret_t = $secret_t . '-' . $val;
		}
		$secret_t = substr($secret_t, 1) . '-' . $secret;
		return hash("sha256", $secret_t);
	}

	function generate_form($secret, $fields)
	{
		$sign = $this->calc_sign($secret, $fields);
		$form = "";
		foreach($fields as $key => $val)
		{
			$form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
		}
		$form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
		return $form;
	}
	public function get($params, $pay)
	{
		$result["text"]      = $pay["text"];
		$result["desc"]      = $pay["desc"];
		
		
		$result["cust_email"] = '';
		if(! empty($pay["details"]["email"]))
		{
			list($result["cust_email"], ) = explode(' ', $pay["details"]["email"]);
			$result["cust_email"] = str_replace('"', '&quot;', $result["cust_email"]);
		}
		
		$result['form'] = $this->generate_form($params["mandarin_msec"],array(
			"email" => $result["cust_email"],
			"merchantId" => $params["mandarin_mid"],
			"orderId" => $pay["id"],
			"price" => $pay["summ"],
			'callbackUrl'=>'http://'.$_SERVER['HTTP_HOST'].'/payment/get/mandarin'
		));
		
		return $result;
	}
}