<?php
$r = $_POST;
if(isset($r['cb_customer_creditcard_number'])):

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

$order_id = $r['orderId'];

function check_sign($secret, $req){
	$sign = $req['sign'];
	unset($req['sign']);
	$to_hash = '';
	if (!is_null($req) && is_array($req)) {
		ksort($req);
		$to_hash = implode('-', $req);
	}

	$to_hash = $to_hash .'-'. $secret;
	$calculated_sign = hash('sha256', $to_hash);
	return $calculated_sign == $sign;
}

$stmt2 = $db->prepare('SELECT * FROM `'.$dbprefix.'jshopping_payment_method` WHERE `payment_id`="1002" LIMIT 1');
$stmt2->execute();
$secret = $stmt2->fetchAll();
$secret = $secret[0]['payment_params'];
$secret = explode('msec=',$secret);
$secret = $secret[1];
$secret = substr($secret,0,-1);

if(!check_sign($secret, $r))die('Not MandarinPay');

switch($r['status']){
	case 'success':
		
		$stmt1 = $db->prepare('UPDATE `'.$dbprefix.'jshopping_orders` SET
			`order_status`="6",
			`invoice_date`="'.date('Y-m-d H:i:s').'",
			`product_stock_removed`="1"
			
			WHERE `order_id`=? LIMIT 1');
		break;//3
	default:
		
		$stmt1 = $db->prepare('UPDATE `'.$dbprefix.'jshopping_orders` SET
			`order_status`="3",
			`invoice_date`="'.date('Y-m-d H:i:s').'",
			`product_stock_removed`="1"
			
			WHERE `order_id`=? LIMIT 1');
}
$stmt1->execute(array($order_id));
endif;