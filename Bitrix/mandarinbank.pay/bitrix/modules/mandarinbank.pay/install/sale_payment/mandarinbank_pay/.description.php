<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$psTitle = Loc::getMessage("PAYSYS_MOD_TITLE");
$psDescription = str_replace("#response_url#", "http://" . $_SERVER['SERVER_NAME'] . "/payment/mandarinbank_pay/st.php", Loc::getMessage("MANDARIN_DESCR"));
$psDescription = str_replace("#suc_url#", "http://" . $_SERVER['SERVER_NAME'] . "/payment/mandarinbank_pay/state.php", $psDescription);

$psTypeDescr = Loc::getMessage("MANDARIN_DESCR");

$arPSCorrespondence = array(
//    "merchant_id" => array(
    "MANDARIN_MERCH_ID" => array(
        "NAME" => Loc::getMessage("MANDARIN_MERCH_ID"),
        "DESCR" => "",
        "VALUE" => "",
        "TYPE" => ""
    ),
//    "secur_key" => array(
    "MANDARIN_SEC_KEY" => array(
        "NAME" => Loc::getMessage("MANDARIN_SEC_KEY"),
        "DESCR" => "",
        "VALUE" => "",
        "TYPE" => ""
    ),
    "email_client" => array(
      "NAME" => Loc::getMessage("email_client"),
      "DESCR" => "",
      "VALUE" => "",
      "TYPE" => "ORDER"
    ),
    "order_id" => array(
        "NAME" => Loc::getMessage("order_id"),
        "DESCR" => "",
        "VALUE" => "order_id",
        "TYPE" => "ORDER"
    ),
    "MANDARIN_PAY_AMOUNT" => array(
        "NAME" => Loc::getMessage("MANDARIN_PAY_AMOUNT"),
        "DESCR" => "",
        "VALUE" => "MANDARIN_PAY_AMOUNT",
        "TYPE" => "ORDER"
    ),
);
