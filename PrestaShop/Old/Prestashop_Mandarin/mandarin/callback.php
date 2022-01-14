<?php

/**
 * Created by Mandarinpay.
 * Date: 29.02.16
 * Time: 21:22
 */
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(_PS_MODULE_DIR_.'mandarinpay/mandarinpay.php');



if ( Tools::getIsset('payment_system') ) 
{//===если вообще был такой параметр===
if ( preg_match('/mandarinpay/i', Tools::getValue('payment_system') ) )
{
	
	//===наполняем массив параметрами, чьи имена не будут меняться========================
	$field = array ( 'merchantId'     => Tools::getValue('merchantId'),
					 'price'          => Tools::getValue('price'),
                     'orderId'        => Tools::getValue('orderId'),
                     'email'          => Tools::getValue('email'),
                     'status'         => Tools::getValue('status'), 
					 'transaction'    => Tools::getValue('transaction'), 
					 'payment_system' => Tools::getValue('payment_system') 	 );
	$sent_sign   =  Tools::getValue('sign');					 
	
	//читаем весь POST запрос
	$postData    = $_POST;
	//а теперь дозаполняем массив $field динамическими полями----
	foreach ( $postData as $key => $value ) 
	{
    	if(  ($key !== 'merchantId')   && ($key !== 'price')  &&  ($key !== 'orderId') &&
             ($key !== 'email') &&  ($key !== 'status')  &&  ($key !== 'transaction') &&
             ($key !== 'payment_system') && ($key !== 'sign')    )
		{
				$field[$key] = $value;		
				// если массив - то удаляем этот элемент из field[]  
				if(  is_array( $field[$key] ) )  unset( $field[$key] );
			
		}				 
	}//--------------------------------------------------------------------	
	

	$secret = Configuration::get('MANDARINPAY_SECRET_KEY');
	ksort($field); 
    $secret_t = ''; 
    foreach($field as $key => $val)
        {                  
            $secret_t = $secret_t . '-' . $val;
		}
    $secret_t = substr($secret_t, 1) . '-' . $secret;
	$our_sign = hash("sha256", $secret_t);

	

	
    //берем как дополнительный параметр - номер транзакции
	$extra_vars = array();
    $extra_vars['transaction_id'] = (string)$field['transaction'];
	
	//берем модуль
	$module = Module::getInstanceByName('mandarinpay');
	
	$amount_paid = $field['price'];
	$mandarinpayOrderDescr = '';
	$id_cart = $field['orderId'];

    if ($sent_sign == $our_sign  &&  $field['status'] == 'success')
    {
		$module->validateOrder($id_cart, 
		                       Configuration::get('MANDARINPAY_STATE_APPROVED'), 
		                       $amount_paid, 
							   'MandarinPay', 
							   $mandarinpayOrderDescr, 
							   $extra_vars);
    }
    else
	{
    

	
	
    exit;
    }

}//==если вообще был такой параметр===
}
else
{
   echo 'This is not callback reply from MandarinPay system!';
   exit;
}