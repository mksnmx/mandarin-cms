<?php
/**
 * MandarinPay hook for Shopkeeper 3.x
 */

//ini_set('display_errors',1);
//error_reporting(E_ALL);
if($hook->getValue('payment') == 'mandarin'){
	$merchantId = '/*Ваш id полученый в системе MandarinPay*/';
	$_SESSION['merchantId'] = $merchantId;
	$secretKey = '/*Ваш секретный ключ полученый в системе MandarinPay*/';
	$url_response = '/*Ссылка на страницу с снипетом get_response*/';
	
	//Определяем параметры сниппета Shopkeeper
	$_SESSION['mandarin_values'] = json_encode($hook->getValues());
	$email = $hook->getValue('email');
	$_SESSION['email'] = $email;
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 4; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
	$_SESSION['orderId'] = $randomString;
	$total = Shopkeeper::$price_total;
	$_SESSION['total'] = $total;
	$_SESSION['sign'] = hash('sha256',$email."-".$merchantId."-".$_SESSION['orderId']."-".$total."-".$secretKey);
	$string = $merchantId.' - '. $secretKey. ' - '. $email. ' - '. $_SESSION['orderId'] . ' - '. $total;
	$modx->log(xPDO::LOG_LEVEL_ERROR, "Mandarin prop: ".$string);
	header("Location: ".$url_response);
	die();
} else {
unset($_SESSION['mandarin_values']);
}

return true;