<?php $r = $_POST;
if(!isset($r['cb_customer_creditcard_number']))die();
$config = include_once($_SERVER{'DOCUMENT_ROOT'}.'/wa-config/db.php');

$host = $config['default']['host'];
$database = $config['default']['database'];
$user = $config['default']['user'];
$password = $config['default']['password'];

$db = new PDO('mysql:host='.$host.';dbname='.$database,$user,$password);
$db->exec("set names utf8");
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

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

$plugin_id = $db->prepare('SELECT * FROM `shop_plugin` WHERE `plugin` = "mandarin" LIMIT 1');
$plugin_id->execute();
$plugin_id = $plugin_id->fetchAll();
$plugin_id = $plugin_id[0]['id'];
$plugin_id = (int)$plugin_id; 

$plugin_secret = $db->prepare('SELECT * FROM `shop_plugin_settings` WHERE `id` = ? AND `name` = ? LIMIT 1');
$plugin_secret->execute(array($plugin_id,'m_secret'));
$plugin_secret = $plugin_secret->fetchAll();
$plugin_secret = $plugin_secret[0]['value'];

if(!check_sign($plugin_secret,$r))die();

if($r['status'] === 'success'){
	$order_id = $r['orderId'];
	$stmt2 = $db->prepare('SELECT * FROM `shop_order_log` WHERE `order_id` = ? LIMIT 1');
	$stmt2->execute(array($order_id));
	$data = $stmt2->fetchAll();
	
	$stmt = $db->prepare('INSERT INTO `shop_order_log` (`id`, `order_id`, `contact_id`, `action_id`, `datetime`, `before_state_id`, `after_state_id`, `text`) VALUES
	(null, '.$order_id.', '.$data[0]['contact_id'].', "pay", "'.date('Y-m-d H:i:s').'", "new", "paid", NULL)');
	$stmt->execute();
	
	$stmt1 = $db->prepare('UPDATE `shop_order` SET
		`paid_quarter`="'. floor((date('n') - 1) / 3) .'",
		`paid_month`="'.date('m').'",
		`paid_year`="'.date('Y').'",
		`update_datetime`="'.date('Y-m-d H:i:s').'",
		`state_id` = "paid",
		`paid_date`="'.date('Y-m-d').'"
		
		WHERE `id`=? LIMIT 1');
	$stmt1->execute(array($order_id));
	
	die('OK');
}
	
//merchantId:392,orderId:3,email:noreple@webasyst2.igolia.tk,price:30.00,status:success,payment_system:mandarinpayv1,sandbox:true,cb_customer_creditcard_number:492950XXXXXX6878,809a00ab-f9b7-4a29-822a-f9405e18b740:4f7d2fc7-8ebd-483a-a08b-fbaa9a78bddd,sign:fc2042f0292e2a2d964dd240b4576b30b5c8caadfd17908d1725aa7f95553601,