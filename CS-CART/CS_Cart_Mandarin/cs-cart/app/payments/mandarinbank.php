<?php
use Tygh\Http;
use Tygh\Registry;
if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = !empty($_REQUEST['orderId']) ? $_REQUEST['orderId'] : 0;

    if ($mode == 'notify') {
            
            $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
            $processor_data = fn_get_payment_method_data($payment_id);

            $merchant_id = $processor_data['processor_params']['merchant_id'];

            $key = $processor_data['processor_params']['sekret_key'];

            $data = $_POST;

            $sign_str = $data['sign'];

            unset($data['sign']);

            ksort($data, SORT_STRING);

            array_push($data, $key);

            $signString = implode('-', $data);
            $sign = hash("sha256", $signString);

            if ($sign === $sign_str && $data['merchantId'] === $merchant_id) {
                wrlog('sign yes');
                $pp_response = array(
                    'order_status' => 'P'
                );

                $pp_response["transaction_id"] = $_REQUEST['transaction'];

                if (fn_check_payment_script('mandarinbank.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                }

            } else {
                
                $pp_response['order_status'] = 'N';
                $pp_response["reason_text"] = __('text_transaction_cancelled');

                if (fn_check_payment_script('mandarinbank.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response, false);
                }
            }

    } elseif ($mode == 'success') {    
            
            $data = $_GET;
            $pp_response["transaction_id"] = $_REQUEST['transaction'];

            if (fn_check_payment_script('mandarinbank.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }
            $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $data['orderId']);
            $processor_data = fn_get_payment_method_data($payment_id);
            
            if ($data['type'] == 'pay' && $data['status'] == "success") {

                $pp_response = array(
                    'order_status' => 'P'
                );

                $pp_response["transaction_id"] = $data['transaction'];
                
                if (fn_check_payment_script('mandarinbank.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                    fn_order_placement_routines('route', $order_id, true);
                }
                header("Location: index.php?dispatch=checkout.complete&order=$order_id");
                exit;

            } else {
               header("Location: checkout/");
                exit;
            }
    }

} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $action_url = "https://secure.mandarinpay.com/Pay";

    $payment_desc = '';

    if (is_array($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $payment_desc .= $order_info['products'][$k]['product'] . ' / ';
        }
    }

    $payment_desc = base64_encode ($payment_desc);
   
    if (empty($processor_data['processor_params']['currency'])) {
        $processor_data['processor_params']['currency'] = 'RUB';
    }

    $secret_key = $processor_data['processor_params']['sekret_key'];

    $order_desc = "#" . $order_info['order_id'];
    $site_name = $_SERVER['SERVER_NAME'];
    $protocol = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : (
	     (
	  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
	   443 == $_SERVER['SERVER_PORT']
	     ) ? 'https://' : 'http://'
	 
	 );
    
    $post_data = array(
        'price' => fn_mandarinbank_get_sum($order_info, $processor_data),
        'merchantId' => $processor_data['processor_params']['merchant_id'],
        'orderId' => $order_info['order_id'],
        'customer_email' => $order_info['email'],
        'callbackUrl' => $protocol.$site_name."/?dispatch=payment_notification.notify&payment=mandarinbank",
        'returnUrl' => $protocol.$site_name."/?dispatch=payment_notification.success&payment=mandarinbank"
    );

    ksort($post_data, SORT_STRING);
    $post_data['secret'] = $secret_key;
    
    $signString = implode('-', $post_data);
   
    $signature = hash("sha256", $signString);

    unset($post_data["secret"]);
    
    $post_data["sign"] = $signature;

    fn_create_payment_form($action_url, $post_data, 'Mandarinbank', false);
}

function fn_mandarinbank_get_sum($order_info, $processor_data)
{
    $price = $order_info['total'];

    if (CART_PRIMARY_CURRENCY != $processor_data['processor_params']['currency']) {
        $currencies = Registry::get('currencies');
        $currency = $currencies[$processor_data['processor_params']['currency']];
        $price = fn_format_rate_value($price, 'F', $currency['decimals'], '.', '', $currency['coefficient']);
    }

    return sprintf('%.2f', $price);
}

function wrlog($content){
    $file = $_SERVER['DOCUMENT_ROOT'].'/logs/log.txt';
    $doc = fopen($file, 'a');

    file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
    fclose($doc);
}

exit;
