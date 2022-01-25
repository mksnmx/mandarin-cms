<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/configuration.php');

$config = new JConfig();

$host = $config->host;
$database = $config->db;
$user = $config->user;
$password = $config->password;
$dbprefix = $config->dbprefix;

$db = new PDO('mysql:host='.$host.';dbname='.$database,$user,$password);
$db->exec("set names utf8");
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

$sql = "INSERT INTO `".$dbprefix."jshopping_payment_method` (`payment_id`, `payment_code`, `payment_class`, `scriptname`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `price`, `price_type`, `tax_id`, `image`, `show_descr_in_email`, `show_bank_in_order`, `order_description`, `name_en-GB`, `description_en-GB`, `name_de-DE`, `description_de-DE`) VALUES (1002, 'mandarin', 'pm_mandarin', 'pm_mandarin', 1, 6, 'mid=1\nmsec=111', 2, '0.00', 1, 1, 'https://secure.mandarinpay.com/favicon.ico', 0, 1, '', 'MandarinBank', '', 'MandarinBank', '');";


$stmt1 = $db->prepare($sql);
$stmt1->execute();

echo 'delete /install-mandarin.php';