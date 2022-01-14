<?php
include_once($_SERVER{'DOCUMENT_ROOT'}.'/configuration.php');

$config = new JConfig();

$host = $config->host;
$database = $config->db;
$user = $config->user;
$password = $config->password;
$dbprefix = $config->dbprefix;

$db = new PDO('mysql:host='.$host.';dbname='.$database,$user,$password);
$db->exec("set names utf8");
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$ru = '';
$_ru = '';
if(count($db->query("SHOW COLUMNS FROM `".$dbprefix."jshopping_payment_method` LIKE 'name_ru-RU'")->fetchAll())){
	$ru = ',`name_ru-RU`';
	$_ru = ',"MandarinBank"';
}


$sql = "INSERT INTO `".$dbprefix."jshopping_payment_method` (`payment_id`, `payment_code`, `payment_class`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `price`, `price_type`, `tax_id`, `image`, `show_descr_in_email`,`name_de-DE`,`name_en-GB`".$ru.") VALUES ('1002', 'mandarin', 'pm_mandarin', '1', '6', 'mid=1\nmsec=111', '2', '0.00', '0', '1', 'https://secure.mandarinpay.com/favicon.ico', '0', 'MandarinBank', 'MandarinBank'".$_ru.");";

$stmt1 = $db->prepare($sql);
$stmt1->execute();

echo 'delete /install-mandarin.php';