<?php

class mandarinBankPayment extends payment
{
    public function validate()
    {
        return true;
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
        $this->order->setPaymentStatus('initialized');

        $form = $this->generate_form($params['secret'], $values = array(
            "email" => $params['email'],
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
        $buffer = outputBuffer::current();
        $buffer->clear();
        $buffer->contentType("text/plain");

        if (!$this->checkSignature()) {
            $buffer->push("failed");
            $buffer->end();
        }

        $this->order->setPaymentStatus('accepted');

        $buffer->end();
    }

    /**
     * Проверяет подпись заказа из платежной системы
     * @return bool
     */
    private function checkSignature()
    {
        $status = getRequest('status');

        if ($status == 'failed') {
            return false;
        }

        $amount = getRequest('price');
        $marchantId = getRequest('merchantId');

        $marchantIdSettings = $this->object->merchantid;
        $amountStore = number_format($this->order->getActualPrice(), 2, '.', '');

        if (($amountStore != $amount || $amount <= 0) || ($marchantId != $marchantIdSettings)) {
            return false;
        }

        if(!$this->check_sign($this->object->secret, $_POST)){
            return false;
        }

        return true;
    }
}

;

?>
