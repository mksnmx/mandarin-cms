<?php

define('MANDARIN_MERCHANTID', 3444);  //Ваш Merchant id
define('MANDARIN_SECRET', 'Do;E^osVa6vagONl');  //Укажите Secret
define('MANDARIN_SUCCESS_ID', 2); //Id страницы успешной оплаты
define('MANDARIN_CANCEL_ID', 4);  //Id страницы не успешной оплаты


if (!class_exists('msPaymentInterface')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/mspaymenthandler.class.php';
}

class Mandarin extends msPaymentHandler implements msPaymentInterface {

    /**
     * InvoiceBox constructor.
     *
     * @param xPDOObject $object
     * @param array $config
     */
    function __construct(xPDOObject $object, $config = array()) {
        parent::__construct($object, $config);

        $siteUrl = $this->modx->getOption('site_url');
        $assetsUrl = $this->modx->getOption('assets_url') . 'components/minishop2/';
        $postUrl = $siteUrl . substr($assetsUrl, 1) . 'payment/mandarinpost.php';
        $paymentUrl = $siteUrl . substr($assetsUrl, 1) . 'payment/mandarin.php';

        $this->config = array_merge(array(
            'callbackUrl' => $paymentUrl,
            'postUrl' => $postUrl,
            'merchantId' => MANDARIN_MERCHANTID,
            'secret' => MANDARIN_SECRET,
            'returnUrl' => $this->modx->makeUrl(MANDARIN_SUCCESS_ID, '', array(), 'full'),
            'cancelUrl' => $this->modx->makeUrl(MANDARIN_CANCEL_ID, '', array(), 'full')
                ), $config);
    }
    function calc_sign($secret, $fields){
	ksort($fields);
	$secret_t = '';
	foreach($fields as $key => $val){
		$secret_t = $secret_t . '-' . $val;
	}
	$secret_t = substr($secret_t, 1) . '-' . $secret;
	return hash("sha256", $secret_t);
    }
    function check_sign($secret, $req){
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
    
        /**
     * @param msOrder $order
     *
     * @return array|string
     */
    public function send(msOrder $order) {
        if ($order->get('status') > 1) {
            return $this->error('ms2_err_status_wrong');
        }
        $params = array(
            'callbackUrl' => $this->config['callbackUrl'],
            'customer_email' => $_POST['email'],
            "merchantId" => $this->config['merchantId'],
            'orderId' => $order->get('id'),
            'price' => number_format($order->get('cost'), 2, '.', ''),
            "returnUrl" => $this->config['returnUrl']
        );
        
        if(!empty($_POST['phone'])&&$_POST['phone']!='') $params = array_merge($params, array('customer_phone' => $_POST['phone']));
        
        $params = array_merge($params, array('sign' => $this->calc_sign($this->config['secret'], $params)));
        
        //$log = print_r($params,1)." \n";
        //file_put_contents(dirname(__FILE__)."/mandarin.log", $log, FILE_APPEND);
        
        $link = $this->config['postUrl'] . '?' . http_build_query($params);
        return $this->success('', array('redirect' => $link));
    }
    /**
     * @param msOrder $order
     * @param array $params
     *
     * @return bool
     */
    public function receive(msOrder $order, $params = array()) {

        if (!($this->check_sign($this->config['secret'], $params))) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[miniShop2:Mandarin] Could not finalize operation: sign key not valid');
            $this->ms2->changeOrderStatus($order->get('id'), 4);
            $log = print_r($params,1)." \n";
            file_put_contents(dirname(__FILE__)."/mandarin.log", 'sign key not valid'." \n".$log, FILE_APPEND);
            echo 'sign key not valid';
            return false;
        }

        if (number_format($order->get('cost'), 2, '.', '') != $params['price']) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[miniShop2:Mandarin] Could not finalize operation: amount not valid');
            $this->ms2->changeOrderStatus($order->get('id'), 4);
            $log = print_r($params,1)." \n";
            file_put_contents(dirname(__FILE__)."/mandarin.log", 'amount not valid'." \n".$log, FILE_APPEND);
            echo 'amount not valid';
            return false;
        }

        $this->ms2->changeOrderStatus($order->get('id'), 2); 
        echo 'OK';
        return true;
    }
    
}
