<?php

class GoodWorkIn_MandarinBank_Model_Mandarinbank extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'mandarinbank';

    protected $_isGateway = true;

    protected $_canAuthorize = false;

    protected $_canCapture = true;

    protected $_canCapturePartial = false;

    protected $_canRefund = false;

    protected $_canVoid = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = true;

    protected $_canUseForMultishipping  = true;

    protected $_canSaveCc = false;

//    protected $_formBlockType = 'mandarinbank/form';
//    protected $_infoBlockType = 'mandarinbank/info';




        public function validate()
        {
            $code = $this->getConfigData('secret');
            $merchant_id = $this->getConfigData('merchant_id');

            if (!$code || !$merchant_id) {
                Mage::throwException(Mage::helper('goodworkinpayment')->__("Somsing wrong!"));
            }
            return parent::validate();
        }

        /**
         * Return Order place redirect url
         *
         * @return string
         */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('mandarinbank', array('_secure' => true));
    }

}
