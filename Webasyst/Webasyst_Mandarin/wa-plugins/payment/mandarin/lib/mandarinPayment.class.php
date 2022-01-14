<?php
class viyModel extends waModel
{

    protected $table = 'wa_contact_emails';

}
class mandarinPayment extends waPayment implements waIPaymentCancel, waIPaymentRefund
{
    const REST = 'rest';
    private $order_id;
    private $callback_protocol;
    private $callback_test = false;
    private $debug = false;
    private $txn;
    private $status;
    private $post;
    private $config;

    public function allowedCurrency(){return true;}

    public function supportedOperations()
    {
        return array(
            self::OPERATION_AUTH_CAPTURE,
            self::OPERATION_HOSTED_PAYMENT_AFTER_ORDER,
        );
    }
	
	private function calc_sign($secret, $fields){
        ksort($fields);
        $secret_t = '';
        foreach($fields as $key => $val)
        {
                $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $secret;
        return hash("sha256", $secret_t);
}

private function generate_form($secret, $fields){
        $sign = $this->calc_sign($secret, $fields);
        $form = "";
        foreach($fields as $key => $val)
        {
                $form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
        }
        $form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
        return $form;
}

    public function payment($payment_form_data, $order_data, $auto_submit = false){
		$messages = array();
		
		$secret = $this->m_secret;
		$mid = $this->m_id;
		

	$modelviy = new viyModel();
		$email = $modelviy->getByField('contact_id',$order_data['contact_id']);
		
		
		
		$messages[] = $this->generate_form($secret, $values = array(
   "callbackUrl" => "http://".$_SERVER['HTTP_HOST']."/payment-mandarin.php",
   "customer_email" => $email['email'],
   "merchantId" => $mid,
   "orderId" => $order_data['order_id'],
   "price" => number_format($order_data['amount'], 2, '.', ''),
   "returnUrl" => "http://".$_SERVER['HTTP_HOST']."/result"
));


		$view = wa()->getView();
		$view->assign('messages', $messages);
		return $view->fetch($this->path.'/templates/paymentRest.html');
    }

    public function cancel($transaction_raw_data){
		
        return false;
    
    }
	
    public function refund($transaction_raw_data){return null;}
	
    
}
