<?php
$r = $_POST;
if(!count($r))die("Request doesn\'t contain POST elements.");

require_once($_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php');
require_once(MODX_MANAGER_PATH . "includes/document.parser.class.inc.php");

$modx = new DocumentParser;
$modx->db->connect();

$modx_dbname = $modx->db->config['dbase'];
$modx_tb_prefix = $modx->db->config['table_prefix'];

$modx_SHK_table = $modx_tb_prefix."manager_shopkeeper";

$the_Orderid	=  intval($r['orderId']);

$message = $modx->db->select('snippet', $modx_tb_prefix.'site_snippets','name="mandarin"');
$message = $modx->db->getValue($message);

$message = explode('$session_msec=',$message);
$message = $message[1];
$message = substr($message,1);
$message = explode(';',$message);
$message = $message[0];
$secret = substr($message,0,-1);

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

if(!check_sign($secret,$r))die('Not MandarinPay!');

if(!$modx->db->getValue($modx->db->select('COUNT(*)',$modx_SHK_table,'id=' . $the_Orderid))&&$r['status']==='success')die('Order not found or status not success');

$modx->db->update(array("status" => 6), $modx_SHK_table, "id = '" . $the_Orderid . "'");
$modx->invokeEvent('OnSHKChangeStatus',array('order_id'=>$the_Orderid,'status'=>6));

die('OK');
?>