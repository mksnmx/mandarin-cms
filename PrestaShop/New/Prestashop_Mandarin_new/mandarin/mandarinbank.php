<?php


use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class MandarinBank extends PaymentModule
{
    const MANDARIN_ID = '';
    const MANDARIN_S_KEY = '';

    public function __construct()
    {
        $this->name = 'mandarinbank';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->author = 'MandarinGuys';
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Mandarin Bank');
        $this->description = $this->l('Прием платежей с помощью кредитной карты с Mandarin Bank');
        $this->confirmUninstall = $this->l('Вы уверенны что хотите удалить все настройки?');

    }

    public function install()
    {
        if (!parent::install()
            OR !$this->registerHook('paymentOptions')
            OR !$this->registerHook('paymentReturn')
            OR !Configuration::updateValue('MANDARIN_ID', '')
            OR !Configuration::updateValue('MANDARIN_S_KEY', '')
            OR !Configuration::updateValue('MANDARIN_PAY_TEXT', 'Оплатить с помощью Mandarin Bank')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return (parent::uninstall()
            AND Configuration::deleteByName('MANDARIN_ID')
            AND Configuration::deleteByName('MANDARIN_S_KEY')
            AND Configuration::deleteByName('MANDARIN_PAY_TEXT')
        );
    }

    public function getContent()
    {
        global $cookie;

        if (Tools::isSubmit('submitMandarinBank')) {
            if ($mandarin_text = Tools::getValue('mandarin_pay_text')) Configuration::updateValue('MANDARIN_PAY_TEXT', $mandarin_text);
            if ($mandarin_id = Tools::getValue('mandarin_id')) Configuration::updateValue('MANDARIN_ID', $mandarin_id);
            if ($mandarin_key = Tools::getValue('mandarin_key')) Configuration::updateValue('MANDARIN_S_KEY', $mandarin_key);
        }

        $html = '<div style="width:550px">
           <p style="text-align:center;">
               <a href="https://mandarinpay.com/" target="_blank">
                <img  src="'. __PS_BASE_URI__ .'modules/mandarinbank/mandarinpay.jpg" alt="mandarinbank" border="0" width="200px" align="center " />
               </a>
            </p>
        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
          <fieldset>
          <legend><img width="20px" src="'. __PS_BASE_URI__ .'modules/mandarinbank/logo.png" />' . $this->l('Настройки') . '</legend>
            <label>
              ' . $this->l('Идентификатор кассы') . '
            </label>
            <div class="margin-form">
              <input type="text" size="40" name="mandarin_id" value="' . Tools::getValue('MANDARIN_ID', Configuration::get('MANDARIN_ID')) . '" required  />
            </div>
            <label>
              ' . $this->l('Секретный ключ') . '
            </label>
            <div class="margin-form">
              <input type="text" size="40" name="mandarin_key" value="' . trim(Tools::getValue('MANDARIN_S_KEY', Configuration::get('MANDARIN_S_KEY'))) . '" required />
            </div>
            <label>
            ' . $this->l('Текст формы оплаты') . '
            </label>
             <div class="margin-form" style="margin-top:5px">
               <input type="text" size="40" name="mandarin_pay_text" value="' . Configuration::get('MANDARIN_PAY_TEXT') . '">
             </div><br>
             <label>
             ' . $this->l('Предварительный просмотр') . '
             </label>
                  <div align="center">' . Configuration::get('MANDARIN_PAY_TEXT') . '&nbsp&nbsp
                  <img width="100px" alt="Оплачивайте с помощью Mandarin Bank" title="Оплачивайте с помощью Mandarin Bank" src="'. __PS_BASE_URI__ .'modules/mandarinbank/mandarinpay.jpg">
                    </div><br>
            <div style="float:right;"><input type="submit" name="submitMandarinBank" class="button btn btn-default pull-right" value="' . $this->l('Сохранить') . '" /></div><div 
            class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />' . $this->l('Информация') . '</legend>
          <p>'.$this->l('Чтобы использовать этот платежный модуль вы должны указать идентификатор кассы, секретный ключ. Для тестирования ознакомьтесь с ').'
          <a href="http://docs.mandarinbank.com#introduction">документацией</a>
          </p>
         </fieldset>
        </div>';

        return $html;
    }

    //Возвращает новый способ оплаты
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        $payment_options = [
            $this->getCardPaymentOption()
        ];
        return $payment_options;
    }

    public function getCardPaymentOption()
    {
        global $cookie, $cart;

        $total = $cart->getOrderTotal();
        $s_key = Configuration::get('MANDARIN_S_KEY');
        $pay_text = Configuration::get('MANDARIN_PAY_TEXT');

        $data = array();
        $data['fields']['merchantId'] = Configuration::get('MANDARIN_ID');
        $data['fields']['customer_email'] = $cookie->email;
        $data['fields']['orderId'] = $cart->id;
        $data['fields']['price'] = number_format(sprintf("%01.2f", $total), 2, '.', '');
        $data['fields']['returnUrl'] = $this->context->link->getPageLink('order-confirmation', null, null, 'key=' . $cart->secure_key . '&id_cart=' . (int)
            ($cart->id) . '&id_module=' . (int)($this->id));
        $data['fields']['callbackUrl'] = Tools::getHttpHost(true) . __PS_BASE_URI__ . 'modules/mandarinbank/validation.php';


        //Формируем подпись
        $fields = $data['fields'];
        ksort($fields);
        $secret_t = '';
        foreach ($fields as $key => $val) {
            $secret_t = $secret_t . '-' . $val;
        }
        $secret_t = substr($secret_t, 1) . '-' . $s_key;
        $signature = hash('sha256', $secret_t);


        $data['fields']['sign'] = $signature;


        $form = [
            'merchantId' => ['name' => 'merchantId',
                'type' => 'hidden',
                'value' => $data['fields']['merchantId'],
            ],

            'orderId' => ['name' => 'orderId',
                'type' => 'hidden',
                'value' => $data['fields']['orderId'],
            ],

            'price' => ['name' => 'price',
                'type' => 'hidden',
                'value' => $data['fields']['price'],
            ],
            'customer_email' => ['name' => 'customer_email',
                'type' => 'hidden',
                'value' => $data['fields']['customer_email'],
            ],

            'returnUrl' => ['name' => 'returnUrl',
                'type' => 'hidden',
                'value' => $data['fields']['returnUrl'],
            ],

            'callbackUrl' => ['name' => 'callbackUrl',
                'type' => 'hidden',
                'value' => $data['fields']['callbackUrl'],
            ],

            'sign' => ['name' => 'sign',
                'type' => 'hidden',
                'value' => $data['fields']['sign'],
            ],

        ];

        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l($pay_text))
            ->setAction('https://secure.mandarinpay.com/Pay')
            ->setInputs($form)
            ->setAdditionalInformation($this->context->smarty->fetch('module:mandarinbank/mandarinbank_info.tpl'))
            ->setLogo( __PS_BASE_URI__ .'modules/mandarinbank/mandarinpay.jpg');

        return $externalOption;
    }

    public function hookPaymentReturn($params)
    {
        if (!empty($_GET)) {
            $this->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'status' => $_GET['status'],
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
            return $this->fetch('module:mandarinbank/mandarinbank_notification.tpl');
        }
    }
}
