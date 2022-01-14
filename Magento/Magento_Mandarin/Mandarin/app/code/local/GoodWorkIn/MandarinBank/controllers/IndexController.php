<?php

class GoodWorkIn_MandarinBank_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
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

    public function returnUrlAction()
    {
        $this->loadLayout();

        file_put_contents('test.txt', serialize($_POST));
        $status = 'failed';
        if (isset($_POST['status'])) {
            $status = $this->getRequest()->getParam('status');
        }

        $amount = $this->getRequest()->getParam('price');

        $order_id = $this->getRequest()->getParam('orderId');

        $marchantId = $this->getRequest()->getParam('merchantId');

        $manderinBankTransactionId = $this->getRequest()->getParam('transaction');

        $customerEmail = $this->getRequest()->getParam('customer_email');
        $customerPhone = $this->getRequest()->getParam('customer_phone');
        $action = $this->getRequest()->getParam('action');
        $merchantIdSystem = Mage::getStoreConfig('payment/mandarinbank/merchant_id');

        $order = Mage::getModel('sales/order')->load($order_id);

        if ($marchantId == $merchantIdSystem && $status != 'failed' && $this->check_sign(Mage::getStoreConfig('payment/mandarinbank/secret'),$_POST)) {

            if (round($order->getGrandTotal(),2) == round($amount,2)) {
                $allowedOrderStates = array(
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
                );
                if (in_array($order->getState(), $allowedOrderStates)) {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Оплата успешна №' . $manderinBankTransactionId);
                    $order->save();
                 }
            }
        }
        $this->renderLayout();
    }

    /**
     * Get frontend checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

}
