<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\IO\Path;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$secret_key = CSalePaySystemAction::GetParamValue("MANDARIN_SEC_KEY");
$merchant_id = CSalePaySystemAction::GetParamValue("MANDARIN_MERCH_ID");

global $config_pay;
$config_pay = array();

function gen_auth($merchantId, $secret){
  $reqid = time() ."_". microtime(true) ."_". rand();
  $hash = hash("sha256", $merchantId ."-". $reqid ."-". $secret);
  return $merchantId ."-".$hash ."-". $reqid;
}
function siteURL(){
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  $port = $_SERVER['SERVER_PORT'] === '80' ? '' : ':'.$_SERVER['SERVER_PORT'];
  $domainName = $_SERVER['HTTP_HOST'].'/';
  return $protocol.$domainName;
}

$xauth = gen_auth($merchant_id,$secret_key);

$price = number_format(CSalePaySystemAction::GetParamValue("MANDARIN_PAY_AMOUNT"), 2, '.', '');

$content = array(
  'payment'=>array(
    'orderId'=>CSalePaySystemAction::GetParamValue("order_id"),
    'action'=>'pay',
    'price'=>$price,
    'orderActualTill'=>date('Y-m-d H:i:s')
  ),
  'customerInfo'=>array(
    'email'=>CSalePaySystemAction::GetParamValue("email_client")
  ),
  'urls'=>array(
    'callback'=> siteURL() .'payment/mandarinbank_pay/st.php'
  )
);
$content = json_encode($content);

$curl = curl_init('https://secure.mandarinpay.com/api/transactions');
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Auth: '.$xauth,'Content-type: application/json') );
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status !== 200 ) {
  die("Error: call to URL $url failed with status $status, response $json_response");
}
curl_close($curl);

$json = json_decode($json_response);

$config_pay['operationId'] = $json->jsOperationId;

include(dirname(__FILE__) . '/tmpl/form.php');
