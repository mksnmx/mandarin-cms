<?php

class MandarinpayPaymentModuleFrontController extends ModuleFrontController {

    public function __construct() {
        parent::__construct();
        $this->context = Context::getContext();
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent() {
        $this->display_column_left = false;
        parent::initContent();

     	//из объекта cart можно вытянуть аж массив продуктов
        //$products = $cart->getProducts();

		//Получаю необходимые для Mandarinpay данные
		//сначала как в CardPay создаю объект класса Cart, а потом через свойство
		//$cart->id  якобы получаю номер заказа
		$cart    = new Cart((int)($this->context->cookie->id_cart));
		
		//такая же тема, для получения email'а я как в CardPay сначала создаю объект класса Customer
		//и как аргумент из cart получаю id кастомера - как это работает на 100% не понимаю еще                       //!!!!!!!!!!!!!!!!
		$customer = new Customer((int)($cart->id_customer));
		
		
		//*******наш блок - подготовка полей для фформы************************
		/*1*/$email = preg_replace("/(')|(\")/", "", $customer->email);
		/*2*/$merchantId = Configuration::get('MANDARINPAY_MERCHANT_ID');
		/*3*/$secret = Configuration::get('MANDARINPAY_SECRET_KEY');
		/*4*/$orderId = 	$cart->id;
		/*5*/$price= $cart->getOrderTotal();
		//==вот так незатейливо вручную без ksort подготоавливаем строку на хэширование
		$stroka = $email.'-'.$merchantId.'-'.$orderId.'-'.$price.'-'.$secret;
		$sign = hash ('sha256', $stroka);
		//**********************************************************************
		
		
		
		//******остатки от кода CardPay чтобы были полные данные****************
		//  iso код валюты - кстати, нужно пораскинуть мозгами как обработать случай заказов в долларах
		$currencyIsoCode = Mandarinpay::getCurrencyIsoCodeById($this->context->cookie->id_currency);
        if ($currencyIsoCode) {
            $header .= " currency='" . preg_replace("/(')|(\")/", "", $currencyIsoCode) . "'";
        }
		//  линк		
		$link = new Link();
		//************************************************************************  
		
		
		
        /* Smarty variables  - передаем параметры - включая 5 наших мандариновских */
        $this->context->smarty->assign(array(
            'mandarinpay_url' => Mandarinpay::getMandarinpayServerUrl(),
            'store_url' => Mandarinpay::getShopDomain(true, true) . __PS_BASE_URI__,
            'back_url' => $link->getPageLink('order', true, NULL),
			'currency' => $currencyIsoCode,
				
			'email' => $email,
			'merchantId' => $merchantId,
			'orderId' => $orderId,
			'price' => $price,
            'sign' => $sign
        ));

        $this->setTemplate('payment-form-ready-for-sending.tpl');
    }
}