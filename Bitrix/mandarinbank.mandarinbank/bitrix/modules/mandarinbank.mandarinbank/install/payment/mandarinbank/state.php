<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(isset($_GET['status'])){
	$orderid = htmlspecialchars($_GET['orderId']);
	if($_GET['status'] == 'success') {
		echo GetMessage("MANDARIN_MESS_OK", Array ("#ORDERID#" => $orderid));
	} else {
		echo GetMessage("MANDARIN_MESS_ERROR", Array ("#ORDERID#" => $orderid));
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
