<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(isset($_GET['status'])){
	if($_GET['status'] == 'success'){
		echo "Ваш заказ №" . $_GET['orderId'] . " успешно оплачен с помощью платежной системы <a href=\"http://mandarinbank.com\" target=\"_blank\">Мандарин Банк</a>\r\n";
		
	} else {
		echo "Произошла ошибка оплаты заказа № <b>" . $_GET['orderId'] . "</b> с помощью платежной системы <a href=\"http://mandarinbank.com\" target=\"_blank\">Мандарин Банк</a>\r\n! Попробуйте еще раз.";
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>