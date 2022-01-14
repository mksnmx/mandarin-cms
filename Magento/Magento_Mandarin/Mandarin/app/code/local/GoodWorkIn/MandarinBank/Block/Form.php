<?php

/**
 * Payment method form base block
 */
class GoodWorkIn_MandarinBank_Block_Form extends Mage_Payment_Block_Form
{
    public function _construct()
    {
        parent::_construct();
    }

    public function getForm(){
        $secret  = Mage::getStoreConfig('payment/mandarinbank/secret');
        $merchantId  = Mage::getStoreConfig('payment/mandarinbank/merchant_id');
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if(!$secret || !$merchantId || !$orderId){
           return 'Нету данных для отображения!!!';
        }


        $order = Mage::getModel('sales/order')->load($orderId);
        $status = $order->getStatus();
        $amount = $order->getGrandTotal();
        $customerEmail = $order->getCustomerEmail();
        if($status!='pending'){
            return 'Заказ не может быть оплачен!';
        }

        $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
        $order->save();

        $button = "<form action='https://secure.mandarinpay.com/Pay' method='POST'>".$this->generate_form($secret, $values = array(
                "email" => $customerEmail,
                "merchantId" => $merchantId,
                "orderId" => $orderId,
                "price" => $amount,
            ))."<input type='submit' value='Оплатить' /></form>";

        return $button;
    }

    private function calc_sign($secret, $fields)
    {
        ksort($fields);
        $secret_t = '';
        foreach($fields as $key => $val)
        {
            $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $secret;
        return hash("sha256", $secret_t);
    }

    private function generate_form($secret, $fields)
    {
        $sign = $this->calc_sign($secret, $fields);
        $form = "";
        foreach($fields as $key => $val)
        {
            $form .= '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
        }
        $form .= '<input type="hidden" name="sign" value="'.$sign.'"/>';
        return $form;
    }

}
