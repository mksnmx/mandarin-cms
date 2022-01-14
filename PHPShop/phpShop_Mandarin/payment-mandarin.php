<?php
$r = $_POST;
if(isset($r['orderId'])&&$r['orderId']){
	$config = $_SERVER['DOCUMENT_ROOT'].'/phpshop/inc/config.ini';

	$ini_array = parse_ini_file($config, true);
	$host = $ini_array['connect']['host'];
	$user_db = $ini_array['connect']['user_db'];
	$pass_db = $ini_array['connect']['pass_db'];
	$dbase = $ini_array['connect']['dbase'];

	$pdo = new PDO(
		'mysql:host='.$host.';dbname='.$dbase,
		$user_db,
		$pass_db
	);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
$config_mandarin = $_SERVER['DOCUMENT_ROOT'].'/phpshop/modules/mandarin/inc/config.ini';
$ini_mandarin_array = parse_ini_file($config_mandarin, true);
$table = $ini_mandarin_array['base']['mandarin_system'];

$stmt = $pdo->prepare('SELECT * FROM `'.$table.'` WHERE `id` = 1 LIMIT 1');
$stmt->execute();
$data = $stmt->fetchAll();
if(!$data)die('No such table for mandarin!');

$secret = $data[0]['merchant_sig'];

	function check_sign($secret, $req)
{
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
if(!check_sign($secret, $r) )die('Not MandarinPay!');

	$stmt = $pdo->prepare("UPDATE `".$ini_array['base']['table_name1']."` SET `statusi`='2' WHERE `id`=? ");
	$stmt->execute(array($r['orderId']));
	
	die('OK');
}die('No data isset!');