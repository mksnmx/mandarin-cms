<?php

if (!isset($modx)) {
	define('MODX_API_MODE', true);
	require dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

	$modx->getService('error', 'error.modError');
}

$modx->error->message = null;
/** @var modX $modx */
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
		
$log = print_r($_POST,1)." \n";
file_put_contents(dirname(__FILE__)."/mandarin.log", $log, FILE_APPEND);
/** @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('minishop2');
$miniShop2->loadCustomClasses('payment');
if (!class_exists('Mandarin')) {
	exit('Error: could not load payment class "Mandarin".');
}
//$context = '';
//$params = array();
/** @var msOrder $order */
$order = $modx->newObject('msOrder');
/** @var msPaymentInterface|Invoicebox $handler */
$handler = new Mandarin($order);

if (!empty($_REQUEST['sign']) && !empty($_REQUEST['orderId'])) {
    if ($order = $modx->getObject('msOrder', $_REQUEST['orderId'])) {
        $handler->receive($order, $_REQUEST);
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR,
            '[miniShop2:Mandarin] Could not retrieve order with id ' . $_REQUEST['orderId']);
    }
}
die();
//$redirect = !empty($_REQUEST['action']) && $_REQUEST['action'] == 'success'? $handler->config['successUrl']:$handler->config['cancelUrl'];
//header('Location: ' . $redirect);