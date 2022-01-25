<?php
/**
* @version      4.13.0 05.11.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();

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
	  
	  $orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
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
        $jshopConfig = JSFactory::getConfig();
        $pm_method = $this->getPmMethod();
        $item_name = sprintf(\JText::_('JSHOP_PAYMENT_NUMBER'), $order->order_number);
        		
        //$email = $pmconfigs['email_received'];
        //$address_override = (int)$pmconfigs['address_override'];
		
        $uri = JURI::getInstance();        
        $liveurlhost = $uri->toString(array("scheme",'host', 'port'));
        $return = $liveurlhost.\JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=".$pm_method->payment_class);
		
        $order->order_total = $this->fixOrderTotal($order);
//echo $return;die();		
        ?>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />            
        </head>
        <body>
		<form id="paymentform" action="https://secure.mandarinpay.com/Pay" method="POST"><?php
		
		echo $this->generate_form($pmconfigs['msec'],array(
		   "callbackUrl" => "http://".$_SERVER['HTTP_HOST']."/payment-mandarin.php",
		   "customer_email" => $order->email,
		   "merchantId" => $pmconfigs['mid'],
		   "orderId" => $order->order_id,
		   "price" => $order->order_total,
		   "returnUrl" => "http://".$_SERVER['HTTP_HOST']."/result-mandarin.php",
		));?></form>        
        <?php echo \JText::_('JSHOP_REDIRECT_TO_PAYMENT_PAGE')?>
        <br>
        <script type="text/javascript">document.getElementById('paymentform').submit();</script>
        </body>
        </html>
        <?php
        die();
	}
    
	function fixOrderTotal($order){
        $total = $order->order_total;
        if ($order->currency_code_iso=='HUF'){
            $total = round($total);
        }else{
            $total = number_format($total, 2, '.', '');
        }
    return $total;
    }
}