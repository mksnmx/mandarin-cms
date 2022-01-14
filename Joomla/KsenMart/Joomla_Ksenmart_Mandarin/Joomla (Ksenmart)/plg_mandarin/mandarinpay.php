<?php defined('_JEXEC') or die('Restricted access');

if (!class_exists('KMPaymentPlugin')) {
	require (JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_ksenmart' . DS . 'classes' . DS . 'kmpaymentplugin.php');
}

class plgKMPaymentMandarinpay extends KMPaymentPlugin {
	
	private  $_params = array(
		'merchantId' => '',
		'secret' => '',
		'email' => ''
	);
	

	var $sample = 0;
	
	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	
	//==функция, окна ввода параметров аккаунта в платежной системе===============
	function onDisplayParamsForm($name = '', $params = null) {
		if ($name != $this->_name) 
		return;
		if (empty($params)) $params = $this->_params;
		$html = '';
		$html.= '<div class="set">';
		$html.= '	<h3 class="headname">' . JText::_('ksm_payment_mandarinpay_algorithm') . '</h3>';
		$html.= '	<div class="row">';
		$html.= '		<label class="inputname">' . JText::_('ksm_payment_mandarinpay_id') . '</label>';
		$html.= '		<input type="text" style="width:250px;" class="inputbox" name="jform[params][merchantid]" value="' . $params['merchantid'] . '">';
		$html.= '	</div>';
		$html.= '	<div class="row">';
		$html.= '		<label class="inputname">' . JText::_('ksm_payment_mandarinpay_secret_key') . '</label>';
		$html.= '		<input type="password" style="width:250px;" class="inputbox" name="jform[params][secret]" value="' . $params['secret'] . '">';
		$html.= '	</div>';
		$html.= '	<div class="row">';
		$html.= '		<label class="inputname">' . JText::_('ksm_payment_mandarinpay_email') . '</label>';
		$html.= '		<input type="text" style="width:250px;" class="inputbox" name="jform[params][email]" value="' . $params['email'] . '">';
		$html.= '	</div>';
		$html.= '</div>';
		return $html;
	}
	
	function onAfterDisplayKSMCartDefault_congratulation($view, &$tpl = null, &$html) {
		if (empty($view->order))  return;
		if (empty($view->order->payment_id))  	return;
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id,params,regions')->from('#__ksenmart_payments')->where('id=' . $view->order->payment_id)->where('type=' . $db->quote($this->_name))->where('published=1');
		$db->setQuery($query);
		$payment = $db->loadObject();
		if (empty($payment)) 
		return;
		if (empty($view->order->region_id)) 
		return;
		if (!$this->checkRegion($payment->regions, $view->order->region_id)) 
		return;
		$payment->params = json_decode($payment->params, true);
		
		//читаем параметры аккаунта в системе MandarinPay
		$_params['merchantId'] =  $payment->params['merchantid'];
		$_params['email'] =  $payment->params['email'];
		$_params['secret'] =  $payment->params['secret'];
		
		//получаем данные об e-mail'е пользователя, если он
		//ввел эти данные в форму
	    $view->user = KSUsers::getUser();
		$email = '';
		if (!empty($view->order->customer_fields->email) )
		    $email = $view->order->customer_fields->email;
		
		
		//**********начинаем создавать HTML-форму для отправки в ПС MandarinPay********
		$html.= '<center>';
		$html.= '	<form action="https://secure.mandarinpay.com/Pay" method="post" class="payment_form">';
		
		$secret = $payment->params['secret'];
			
		//генерируем основную часть HTML-формы
		//с помощью приватной функции
	   	$f = plgKMPaymentMandarinpay::_getHtmlForm($secret, 
		                                           $values = array( "email" => $email,
																    "merchantId" => $_params['merchantId'],
																	"orderId" => $view->order->id,
																	"price" => $view->order->costs['total_cost'])   );
	    $html.= $f;
 
        //и дописываем низ HTML-формы
		$html.= '	<br><br>   <input type="submit" value="' . JText::_('ksm_payment_mandarinpay_pay');
		$html.=	'" class="button btn-success btn-large noTransition">';
		$html.= '	</form>';
		$html.= '</center>';
		//********HTML-форма готова*******************************************************
	}
	
	
	//= Функция onPayOrder() вызывается при событии order_payment===============================
	function onPayOrder() {
	
		$app   = JFactory::getApplication();
        $input = $app->input;
		$postData    = $input->getArray($_POST);
		
		//проверяем, что это точно форма от MandarinPay=======================================
		$payment_system = $input->get('payment_system', null, 'string');
		if ( $payment_system !== 'mandarinpayv1') $app->close('WMI_RESULT=ERROR');
		
				
		//открываем базу данных
	    $db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id,params')->from('#__ksenmart_payments')->where('type = "mandarinpay"	')->where('published=1');
		$db->setQuery($query);
		$payment = $db->loadObject();
		//читаем значение Secret
		$payment->params = json_decode($payment->params, true);
		$secret = $payment->params['secret'];
	
	

	    //===наполняем массив параметрами, чьи имена не будут меняться========================
		$field = array ( 'merchantId'     => $input->get('merchantId', null, 'string'),
						 'price'          => $input->get('price', null, 'string'),
                         'orderId'        => $input->get('orderId', null, 'string'),
                         'email'          => $input->get('email', null, 'string'),
                         'status'         => $input->get('status', null, 'string'), 
						 'transaction'    => $input->get('transaction', null, 'string'), 
						 'payment_system' => $payment_system 	 );
						 
		//отдельно читаем значение sign - хэша от платежной системы------------
		$sent_sign   =  $input->get('sign', null, 'string');
        
		//а теперь дозаполняем массив $field динамическими полями----
		foreach ( $postData as $key => $value ) 
		{
    		if(  ($key !== 'merchantId')   && ($key !== 'price')  &&  ($key !== 'orderId') &&
                 ($key !== 'email') &&  ($key !== 'status')  &&  ($key !== 'transaction') &&
                 ($key !== 'payment_system') && ($key !== 'sign')    )
			{
				$field[$key] = $value;		
                    //!!! вынужденная мера, в массив $_POST попадал некий параметрами
					// kmdiscounts = array ()    http://clip2net.com/s/3uIxfJx
					// пока не разобрались отчего, я ввел проверку на массив
					// если массив - то удаляем этот элемент из field[]  
					if(  is_array( $field[$key] ) )  unset( $field[$key] );
			
			}				 
	    }//--------------------------------------------------------------------	
		//=======================================================================================
		


        //проверяем, что мы получили полноформатный ответ, включая наличие хэша от платежной системы
		if ( empty($field['merchantId']) || empty($field['orderId'])  ||  empty($field['price']) ||   empty($sent_sign)  ) {
			$app->close('WMI_RESULT=ERROR'); 		}
	

		//получаем свой hash на присланные данные
		$our_sign = plgKMPaymentMandarinpay::_getHash($secret, $field);

		//если хэши сходятся и статус транзакции success
		//записываем в таблицу с заказами статус "Оплачено" или status_id = 5
		if ( $sent_sign === $our_sign  &&  $field['status'] === 'success' )		
	    {
			$query = $db->getQuery(true);
			$query->update('#__ksenmart_orders')->set('status_id=5')->where('id=' . $field['orderId']);  //!!!!!!!!!!! 5 - это статус  оплаченного товара!!!!
			$db->setQuery($query);
			$db->query();
			$app->close('WMI_RESULT=OK');
		}
		else
			$app->close('WMI_RESULT=RETRY');
	

	}//===========================================================================
	
	
	
	//====функция расчета хэша по алгоритму sha256================================
	private function _getHash ($secret, $fields )
	{
		ksort($fields); 
        $secret_t = ''; 
        foreach($fields as $key => $val)
        {                  
            $secret_t = $secret_t . '-' . $val;
		}
           //убираем первый символ «-»
            $secret_t = substr($secret_t, 1) . '-' . $secret;

		//считаем хэш по алгоритму sha256 и возвращаем его
		return hash("sha256", $secret_t);
	}//===============================================================================
	
	//====функция генерации HTML-формы для отправки в платежную систему===============
	private function _getHtmlForm ($secret, $fields )
	{
		$sign = plgKMPaymentMandarinpay::_getHash($secret, $fields);
        $form = '';
        foreach($fields as $key => $val)
        {       
            $form  = $form . '<input type="hidden" name="'.$key.'" value="';
			//htmlspecialchars — Преобразует специальные символы в HTML-сущности
			$form .= htmlspecialchars($val) . '"/>'."\n";
        }
		
		//и в конце создаем главный hidden input с хэшем
        $form .= '<input type="hidden" name="sign" value="'.$sign.'"/>';
        return $form;
	}//===============================================================================
	
	
	 
	
	
}