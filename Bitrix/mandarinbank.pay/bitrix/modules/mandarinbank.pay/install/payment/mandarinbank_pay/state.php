<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if(isset($_GET['status'])){
    if($_GET['status'] === 'success'){
        echo GetMessage("MANDARIN_MESS_OK");

    } else {
        echo GetMessage("MANDARIN_MESS_ERROR");
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
