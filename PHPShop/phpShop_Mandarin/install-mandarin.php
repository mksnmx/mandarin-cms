<?php
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


$stmt = $pdo->prepare("INSERT IGNORE INTO `".$ini_array['base']['payment_systems']."` (`id`, `name`, `path`, `enabled`, `num`, `message`, `message_header`, `yur_data_flag`, `icon`) VALUES (10002, 'MandarinBank', 'mandarin', '1', 100, '<img src=\"/UserFiles/Image/Trial/rabbit.png\">', '', '0', 'https://secure.mandarinpay.com/favicon.ico');");
$stmt->execute();


echo 'delete /install-mandarin.php';