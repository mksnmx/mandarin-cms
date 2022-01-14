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

echo $result["text"];
?>

<form action="https://secure.mandarinpay.com/Pay " method="POST"> 
	<input type="hidden" name="merchantId" value="<?php echo $result["shopid"]; ?>" />
	<input type="hidden" name="price" value="<?php echo $result["summ"]; ?>" /> 
	<input type="hidden" name="orderId" value="<?php echo $result["order_id"]; ?>" />
	<input type="hidden" name="email" value="<?php echo $result["cust_email"]; ?>" />
	<input type="hidden" name="sign" value="<?php echo $result["sign"]; ?>" /> 
	<input type="submit" value="Оплатить через MandarinPay" /> 
</form>