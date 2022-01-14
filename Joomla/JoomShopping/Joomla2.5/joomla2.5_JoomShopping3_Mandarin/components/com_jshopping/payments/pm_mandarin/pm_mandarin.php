<?php
defined('_JEXEC') or die('Restricted access');

class pm_mandarin extends PaymentRoot{
    
    function showPaymentForm($params, $pmconfigs){
        include(dirname(__FILE__)."/paymentform.php");
    }

	//function call in admin
	function showAdminFormParams($params){
	  $array_params = array('mid', 'msec');
	  foreach ($array_params as $key){
	  	if (!isset($params[$key])) $params[$key] = '';
	  }
	  $orders = JModel::getInstance('orders', 'JshoppingModel'); //admin model
      include(dirname(__FILE__)."/adminparamsform.php");
	}
	
	function calc_sign($secret, $fields){
		ksort($fields);
		$secret_t = '';
		foreach($fields as $key => $val)
		{
				$secret_t = $secret_t . '-' . $val;
		}
		$secret_t = substr($secret_t, 1) . '-' . $secret;
		return hash("sha256", $secret_t);
	}

	function generate_form($secret, $fields){
		$sign = $this->calc_sign($secret, $fields);
		$form = "";
		foreach($fields as $key => $val)
		{
				$form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
		}
		$form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
		return $form;
	}


	function showEndForm($pmconfigs, $order){
        $order->order_total = $this->fixOrderTotal($order);
		
        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />            
        </head>
        <body>
		<form id="paymentform" action="https://secure.mandarinpay.com/Pay" method="POST"><?=$this->generate_form($pmconfigs['msec'],array(
		   "callbackUrl" => "http://".$_SERVER['HTTP_HOST']."/payment-mandarin.php",
		   "customer_email" => $order->email,
		   "merchantId" => $pmconfigs['mid'],
		   "orderId" => $order->order_id,
		   "price" => $order->order_total,
		   "returnUrl" => "http://".$_SERVER['HTTP_HOST']."/result-mandarin.php",
		))?></form>        
        <?php print _JSHOP_REDIRECT_TO_PAYMENT_PAGE?>
        <br>
        <script type="text/javascript">document.getElementById('paymentform').submit();</script>
        </body>
        </html>
        <?php
        die();
	}
    
    function getUrlParams($pmconfigs){
        $params = array(); 
        $params['order_id'] = JRequest::getInt("custom");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = $pmconfigs['checkdatareturn'];
    return $params;
    }
    
    function fixOrderTotal($order){
        $total = $order->order_total;
        if ($order->currency_code_iso=='HUF'){
            $total = round($total);
        }
    return $total;
    }
}