<?php

if(isset($_GET['orderId'])&&isset($_GET['type'])&&$_GET['type'] === 'pay'&&isset($_GET['status'])&&$_GET['status'] === 'success'):
/*
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

$order_id = $_GET['orderId'];

$stmt1 = $db->prepare('UPDATE `'.$dbprefix.'jshopping_orders` SET
	`order_created`="1",
	`invoice_date`="'.date('Y-m-d H:i:s').'",
	`product_stock_removed`="1"
	
	WHERE `order_id`=? LIMIT 1');
$stmt1->execute(array($order_id));
*/

$return = 'Location: '.'/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_mandarin';

header($return);

endif;