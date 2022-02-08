<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main\IO\Path;
include(GetLangFileName(dirname(__FILE__) . "/", "/lang.php"));

$merchant_id = CSalePaySystemAction::GetParamValue("MANDARIN_MERCH_ID");
$secret_key = CSalePaySystemAction::GetParamValue("MANDARIN_SEC_KEY");

//proccess request
if (count($_REQUEST)) {

    if($_POST['merchantId']){
        
        $data = $_REQUEST;

        $request_sign = $data['sign'];
        unset($data['sign']);
        ksort($data);
        array_push($data, $secret_key);
        $signString = implode('-', $data);
        $sign = hash("sha256",$signString);

        if ($sign === $request_sign && $data['merchantId'] === $merchant_id) {

            $order_id = $data['orderId'];

            if (!($arOrder = CSaleOrder::GetByID(intval($order_id)))) {
                header('HTTP/1.0 400 Bad Request');
                exit;
            }

            CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);

            if ($arOrder["PAYED"] == "N") {
                $arFields = array(
                    "PS_STATUS" => "Y",
                    "PS_STATUS_CODE" => "-",
                    "PS_STATUS_DESCRIPTION" => $order_id,
                    "PS_STATUS_MESSAGE" => $data['status'],
                    "PS_SUM" => $data['price'],
                    "PS_CURRENCY" => "RUB",
                    "PS_RESPONSE_DATE" => date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
                    "USER_ID" => $arOrder["USER_ID"],
                );

                if (CSaleOrder::Update($arOrder["ID"], $arFields)) {
                    if ($arOrder["PRICE"] == $data['price']) {
                        CSaleOrder::PayOrder($arOrder["ID"], "Y", false);
                        CSaleOrder::StatusOrder($arOrder["ID"], "F");
                        exit("OK");
                    } else {
                        header('HTTP/1.0 400 Bad Request');
                        exit();
                    }
                }
            } else {
                header('HTTP/1.0 400 Bad Request');
                exit();
            }  
            
        } else {
            header('HTTP/1.0 400 Bad Request');
            exit();
        }
    }   
}
function wrlog($data){
    $file = $_SERVER['DOCUMENT_ROOT'] . '/log.txt';
    $doc = fopen($file, 'a');
    file_put_contents($file, PHP_EOL . $data, FILE_APPEND);
    fclose($doc);
}
exit();
