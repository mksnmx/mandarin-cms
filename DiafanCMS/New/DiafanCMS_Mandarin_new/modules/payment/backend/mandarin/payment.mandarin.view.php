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
?><form action="https://secure.mandarinpay.com/Pay" method="POST"><?=$result['form']?><input type="submit" value="Оплатить через Mandarin"></form>