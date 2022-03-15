<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/mandarinbank.php');
$mandarin = new MandarinBank();

if (count($_POST) && isset($_POST['sign'])) {
    $cart = new Cart((int)$_POST['orderId']);

    $merchantId = Configuration::get('MANDARIN_ID');

    if ($_POST['status'] == 'success' && $merchantId== $_POST['merchantId']) {

        $secret_key = Configuration::get('MANDARIN_S_KEY');

        $request = $_POST;
        $request_sign = $request['sign'];
        unset($request['sign']);

        //формируем цифровую подпись
        $fields = $request;
        ksort($fields);
        $secret_t = '';
        foreach ($fields as $key => $val) {
            $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $secret_key;
        $signature = hash('sha256', $secret_t);


        if ($request_sign == $signature) {
            $mandarin->validateOrder($cart->id, _PS_OS_PAYMENT_, $_POST['price'], $mandarin->displayName, NULL,array('transaction_id'=>$_POST['transaction']));
            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$mandarin->id.'&id_order='.$mandarin->currentOrder.'&key='.$order->secure_key);
            $order = new Order($mandarin->currentOrder);
            header('Status: 200 Ok');
        } else {
            Tools::redirectLink(__PS_BASE_URI__ . 'order.php');
        }
    }else{
        $mandarin->validateOrder($cart->id, _PS_OS_PAYMENT_, false, $mandarin->displayName, NULL,array('transaction_id'=>$_POST['transaction']));
        $order = new Order($mandarin->currentOrder);
    }

}


