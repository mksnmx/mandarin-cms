<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$psID = 0;  // ������� ID ��������� ������� (� ������� /bitrix/admin/sale_pay_system.php - ������ �������
$ptID = 0;  // ������� ID ����� ������������ (1)
$ordID = 0;  

if (isset($_REQUEST['orderId'])) {
	$ordID = $_REQUEST['orderId'];
	if ($ordID && CModule::IncludeModule('sale')) {
		$arOrder = CSaleOrder::GetByID($ordID);
		$psID = $arOrder['PAY_SYSTEM_ID'];
		$ptID = $arOrder['PERSON_TYPE_ID'];
	}
}

$APPLICATION->IncludeComponent('bitrix:sale.order.payment.receive','',
	Array(
		'PAY_SYSTEM_ID' => $psID,
		'PERSON_TYPE_ID' => $ptID
	)
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>