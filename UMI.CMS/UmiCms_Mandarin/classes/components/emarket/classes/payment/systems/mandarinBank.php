<?php
use UmiCms\Service;

class mandarinBankPayment extends payment
{
    public function validate()
    {
        return true;
    }
    
    public static function getOrderId() {
        //$rawRequest = Service::Request()->getRawBody();
	//$request = json_decode($rawRequest, true);
        //Test
        //file_put_contents('./log.txt', getRequest('orderId'));
        //End Test
	//return $request['orderId'];
        return (int) getRequest('orderId');
    }

    private function check_sign($secret,$fields)
    {
        $signAnswer = $fields['sign'];
        $sign = $this->calc_sign($secret,$fields);
        return $sign == $signAnswer;
    }

    private function calc_sign($secret, $fields)
    {
        if (isset($fields['sign'])) {
            unset($fields['sign']);
        }
        ksort($fields);
        $secret_t = '';
        foreach ($fields as $key => $val) {
            $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $secret;
        return hash("sha256", $secret_t);
    }

    private function generate_form($secret, $fields)
    {
        $sign = $this->calc_sign($secret, $fields);
        $form = "";
        foreach ($fields as $key => $val) {
            $form .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($val) . '"/>' . "\n";
        }
        $form .= '<input type="hidden" name="sign" value="' . $sign . '"/>';
        return $form;
    }

    public function process($template = 'default')
    {
        $this->order->order();

        $merchantid = $this->object->merchantid;
        $secret = $this->object->secret;

        if (!strlen($merchantid) || !strlen($secret))
            throw new publicException(getLabel('error-payment-wrong-settings'));

        $sum = number_format($this->order->getActualPrice(), 2, '.', '');
        $customer = customer::get();
        $customer = $customer->getObject();

        $params = array();
        $params['merchantid'] = $merchantid;
        $params['secret'] = $secret;

        $params['email'] = (string)$customer->getValue('email');
        if ($params['email'] === '') {
            $params['email'] = (string)$customer->getValue('e-mail');
        }

        $params['sum'] = $sum;
        $params['action'] = 'https://secure.mandarinpay.com/Pay';
        $params['order_id'] = $this->order->id;
        $httpScheme = getSelectedServerProtocol();
        $params['notify_url'] = $httpScheme . '://' . $_SERVER['SERVER_NAME'] . '/emarket/gateway/';
        //$params['notify_url'] = $httpScheme . '://' . $_SERVER['SERVER_NAME'] . '/test.php';
        $params['return_url'] = $httpScheme . '://' . $_SERVER['SERVER_NAME'] . '/emarket/personal/';
        $this->order->setPaymentStatus('initialized');

        $form = $this->generate_form($params['secret'], $values = array(
            "customer_email" => $params['email'],
            "callbackUrl" => $params['notify_url'],
            "returnUrl" => $params['return_url'],
            "merchantId" => $params['merchantid'],
            "orderId" => $params['order_id'],
            "price" => $sum,
        ));


        $params['form'] = $form;
        list($form_block) = emarket::loadTemplates(
            "emarket/payment/mandarinBank/" . $template,
            "form_block"
        );


        return emarket::parseTemplate($form_block, $params);
    }

    public function poll()
    {
        //$rawRequest = Service::Request()->getRawBody();
	//$request = json_decode($rawRequest, true);
        
        $order = $this->order;
//file_put_contents('./log.txt', '1');
        $buffer = Service::Response()->getCurrentBuffer();
//file_put_contents('./log.txt', '2');
        if (!$this->checkSignature()) {
            $buffer->push("failed");
            $buffer->end();
        }
//file_put_contents('./log.txt', '3');
        $order->setPaymentStatus('accepted');
        $order->setPaymentDocumentNumber(getRequest('transaction'));
//file_put_contents('./log.txt', getRequest('transaction'));
        $order->commit();

	$buffer->clear();
	$buffer->contentType('text/plain');
	$buffer->push('OK - '.$order->getPaymentStatus());

        return $buffer;
    }

    /**
     * Проверяет подпись заказа из платежной системы
     * @return bool
     */
    private function checkSignature()
    {
        //$rawRequest = Service::Request()->getRawBody();
	//$request = json_decode($rawRequest, true);
        
        //$status = $request['status'];
        
        $status = getRequest('status');

        if ($status == 'failed') {
            return false;
        }

        //$amount = $request['price'];
        //$marchantId = $request['merchantId'];
        $amount = getRequest('price');
        $marchantId = getRequest('merchantId');

        $marchantIdSettings = $this->object->merchantid;
        $amountStore = number_format($this->order->getActualPrice(), 2, '.', '');

        if (($amountStore != $amount || $amount <= 0) || ($marchantId != $marchantIdSettings)) {
            return false;
        }
//file_put_contents('./log.txt', '22');
        if(!$this->check_sign($this->object->secret, $_POST)){
            return false;
        }
//file_put_contents('./log.txt', '23');
        return true;
    }
}

;

?>