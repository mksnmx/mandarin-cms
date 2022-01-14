<?php

defined(IN_PARSER_MODE) or die();
if(!$session_mid||!$session_msec)die();
if(!isset($_GET['status'])):

$modx_tb_prefix = $modx->db->config['table_prefix'];
$modx_base_dir = $modx->config['rb_base_dir'];
$modx_charset = $modx->config['modx_charset'];
$modx_userLogged =  isset($_SESSION['webValidated']) ? true : false;
$modx_thisPage = $modx->makeUrl($modx->documentIdentifier, '', '', 'full');
$modx_thisURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$modx_qs = strpos($modx_thisURL, "?")===false ? '?' : '&amp;';
$modx_SHK_table = $modx_tb_prefix."manager_shopkeeper";

$payment_modx_orderid = isset($payment_modx_orderid) ? $payment_modx_orderid : (isset($_SESSION['shk_order_id']) ? $_SESSION['shk_order_id'] : '');
$payment_price = isset($payment_price) ? $payment_price : (isset($_SESSION['shk_order_price']) ? $_SESSION['shk_order_price'] : '');
$payment_useremail = isset($payment_useremail) ? $payment_useremail : (isset($_SESSION['shk_order_user_email']) ? $_SESSION['shk_order_user_email'] : '');
$payment_price = isset($payment_price) ? number_format(floatval($payment_price),2,'.','') : '';

$modx_callbackPage = 'http://'.$_SERVER['HTTP_HOST'].'/assets/snippets/mandarin/callback.php';

function calc_sign($secret, $fields){
	ksort($fields);
	$secret_t = '';
	foreach($fields as $key => $val){
		$secret_t = $secret_t . '-' . $val;
	}
	$secret_t = substr($secret_t, 1) . '-' . $secret;
	return hash("sha256", $secret_t);
}

function generate_form($secret, $fields){
	$sign = calc_sign($secret, $fields);
	$form = "";
	foreach($fields as $key => $val){
		$form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
	}
	$form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
	return $form;
}
?><form action="https://secure.mandarinpay.com/Pay" method="POST"><?=generate_form($session_msec,array(
   "email" => $payment_useremail,
   "merchantId" => $session_mid,
   "orderId" => $payment_modx_orderid,
   "price" => $payment_price,
   'returnUrl'=>$modx_thisPage,
   'callbackUrl'=>$modx_callbackPage
));

?><input type="submit" value="Оплатить" /></form><?php

endif;