<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\IO\Path; 
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $config_pay;

$config_pay = array();

$action = "https://secure.mandarinpay.com/Pay";
$secret_key = CSalePaySystemAction::GetParamValue("MANDARIN_SEC_KEY");

$config_pay['merchantId'] = CSalePaySystemAction::GetParamValue("MANDARIN_MERCH_ID");
$config_pay['orderId'] = CSalePaySystemAction::GetParamValue("order_id");
$config_pay['price'] = number_format(CSalePaySystemAction::GetParamValue("MANDARIN_PAY_AMOUNT"), 2, '.', '');
$config_pay['customer_email'] = CSalePaySystemAction::GetParamValue("email_client");
$config_pay['callbackUrl'] = "http://" . $_SERVER['SERVER_NAME'] . "/payment/mandarinbank/st.php";
$config_pay['returnUrl'] = "http://" . $_SERVER['SERVER_NAME'] . "/payment/mandarinbank/state.php";


ksort($config_pay);

$config_pay['secret'] = $secret_key;

$signString = implode('-', $config_pay);

$signature = hash("sha256", $signString);
unset($config_pay["secret"]);
$config_pay["sign"] = $signature;

include(dirname(__FILE__).'/tmpl/form.php');
