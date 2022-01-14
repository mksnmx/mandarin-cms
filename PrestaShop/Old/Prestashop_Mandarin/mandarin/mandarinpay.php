<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class MandarinPay, payment module
 */
class Mandarinpay extends PaymentModule {
    const DEFAULT_SERVER_URL = 'https://secure.mandarinpay.com/Pay';
    const BOTH_WITHOUT_SHIPPING = 4;

    const STATE_APPROVED = 2;
    const STATE_PENDING  = 2;
    /**
     * Constructor
     */
    public function __construct() {
		$this->name = 'mandarinpay';	
		$this->tab  = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author  = 'MandarinPay';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('MandarinPay');
		$this->description = $this->l('Accept payments by MandarinPay.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
	}

    /**
     * Module installation
     */
    public function install() {
		if (!parent::install() || !$this->registerHook('payment')) {
			return false;
        }

        // set default configuration values
        Configuration::updateValue('MANDARINPAY_SERVER_URL', self::DEFAULT_SERVER_URL);
        Configuration::updateValue('MANDARINPAY_HOLD_ONLY', 0);
 		Configuration::updateValue('MANDARINPAY_MERCHANT_ID', '');
        Configuration::updateValue('MANDARINPAY_SECRET_KEY', '');
        Configuration::updateValue('MANDARINPAY_STATE_APPROVED', Mandarinpay::STATE_APPROVED);
        Configuration::updateValue('MANDARINPAY_STATE_PENDING',  Mandarinpay::STATE_PENDING);
		return true;
	}

    /**
     * Module uninstall
     */
    public function uninstall() {
		if (!parent::uninstall()) {
			return false;
        }

        // delete configuration values
        Configuration::deleteByName('MANDARINPAY_SERVER_URL');
        Configuration::deleteByName('MANDARINPAY_HOLD_ONLY');
		Configuration::updateValue('MANDARINPAY_MERCHANT_ID', '');
        Configuration::deleteByName('MANDARINPAY_SECRET_KEY');

        Configuration::deleteByName('MANDARINPAY_STATE_APPROVED');
        Configuration::deleteByName('MANDARINPAY_STATE_PENDING');

		return true;
	}

    /**
     * Module configuration in admin
     */
    public function getContent() {
        global $cookie;

        $lang = new Language((int)($cookie->id_lang));
        $states = OrderState::getOrderStates($lang->id);

        $errors = array();

        /* Logo */
        $output = '<p><img src="'.__PS_BASE_URI__.'modules/mandarinpay/logo-real.png" alt="MandarinPay" width="206" height="45" /></p><br/>';

        /* On form submit */
        if (Tools::isSubmit('submitMandarinpayConfig')) {
            Configuration::updateValue('MANDARINPAY_SERVER_URL', Tools::getValue('server_url'));
            Configuration::updateValue('MANDARINPAY_HOLD_ONLY', Tools::getValue('hold_only'));
      		Configuration::updateValue('MANDARINPAY_MERCHANT_ID', Tools::getValue('merchant_id'));
            Configuration::updateValue('MANDARINPAY_SECRET_KEY', Tools::getValue('secret_key'));
            Configuration::updateValue('MANDARINPAY_STATE_APPROVED', Tools::getValue('state_approved'));
            Configuration::updateValue('MANDARINPAY_STATE_PENDING', Tools::getValue('state_pending'));

            $output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Configuration updated').'</div>';
        }

        /* Determine MandarinPay server URL */
        $serverUrl = $this->getMandarinpayServerUrl();

        /* Display errors */
        if (count($errors) > 0) {
            $output .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB; padding: 10px;">';
            foreach ($errors AS $error) {
                $output .= '<li>' . $error . '</li>';
            }
            $output .= '</ul>';
        }

		$shop_url = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
        $module_url = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';

        $output .= '<fieldset><legend> '.$this->l('Help').'</legend>';
        //==в более человеский вид привел вывод ссылок - в полях input'ах
		//==для того, чтобы за один клик можно было скопипастить URL и вставить в личном кабинете
		$output .= '<p><b>'.$this->l('Callback Url').': </b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
		            '<input type="text" name="callback_url" value="' .$module_url.'callback.php" style="width: 500px;" /><br/>'.'</p>';
        $output .= '<p><b>'.$this->l('User Redirect Url').': </b>&nbsp;&nbsp;&nbsp;'.
		            '<input type="text" name="redirect_url" value="' .$shop_url.'" style="width: 500px;" /><br/>'.'</p>';
		//===
		
        $output .= '<p>'.$this->l('Tell about this pages to the Mandarinpay company manager').'</p>';

        $output .= '<p>'.$this->l('Contact us on').' <a href="http://www.mandarinbank.com" target="_blank">www.mandarinbank.com</a> </p>';
        $output .= '<div class="clear">&nbsp;</div>
		</fieldset>
		<div class="clear">&nbsp;</div>';


        /* Display settings form */
        $output .= '<form method="post" action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" id="form-settings">';
        $output .= '    <fieldset>';
        $output .= '    <legend><img src="../img/admin/tab-preferences.gif" alt="' . $this->l('Module configuration') . '" title="' . $this->l('Module configuration') . '" />' . $this->l('Module configuration') . '</legend>';

        $output .= '        <label for="server_url">'.$this->l('MandarinPay server URL').'</label>';
        $output .= '        <div class="margin-form">';
        $output .= '            <input type="text" name="server_url" value="' . Tools::getValue('server_url', $serverUrl) . '" style="width: 300px;" /><br/>';
        $output .= '        </div>';




        $output .= '        <label for="merchant_id">'.$this->l('MerchantId').'</label>';
        $output .= '        <div class="margin-form">';
        $output .= '            <input type="text" name="merchant_id" value="' . Tools::getValue('merchant_id', Configuration::get('MANDARINPAY_MERCHANT_ID')) . '" style="width: 300px;" /><br/>';
        $output .= '            <p>'.$this->l('This is your shop ID.').'</p>';
        $output .= '        </div>';

        $output .= '        <label for="secret_key">'.$this->l('Secret key').'</label>';
        $output .= '        <div class="margin-form">';
        $output .= '            <input type="text" name="secret_key" value="' . Tools::getValue('secret_key', Configuration::get('MANDARINPAY_SECRET_KEY')) . '" style="width: 300px;" /><br/>';
        $output .= '            <p>'.$this->l('Merchant secret key. You can generate it in your merchant cabinet.').'</p>';
        $output .= '        </div>';

        // MANDARINPAY_STATE_APPROVED
        $output .= '        <label for="state_approved">'.$this->l('State APPROVED').'</label>';
        $output .= '        <div class="margin-form">';
        $output .= '        <select name="state_approved">';

        foreach($states as $state)
        {
            $output .= '<option value="'.$state['id_order_state'].'" ';
            $output .= 'style="background-color:'.$state['color'].'" ';
            $output .= ($state['id_order_state'] == Tools::getValue('state_approved', Configuration::get('MANDARINPAY_STATE_APPROVED'))) ? ' selected' : '';
            $output .= '>'.$state['name'].'</option>';
        }

        $output .= '        </select>';
        $output .= '            <p>'.$this->l('Mapping MandarinPay APPROVED state.').'<br>'.$this->l('You can have your custom APPROVED state.').'</p>';
        $output .= '        </div>';

        $output .= '        <div class="margin-form">';
		$output .= '            <input name="submitMandarinpayConfig" type="submit" class="button" value="'.$this->l('Submit').'">';
        $output .= '        </div>';
        $output .= '    </fieldset>';
        $output .= '</form>';

        return $output;
    }

    /**
     * Payment form during the payment process
     */
    public function hookPayment($params) {
        /* Check the configuration parameters existence first */
        if ((false === Configuration::get('MANDARINPAY_SERVER_URL')) ||
                (false === Configuration::get('MANDARINPAY_HOLD_ONLY')) ||
                (false === Configuration::get('MANDARINPAY_MERCHANT_ID')) ||
                (false === Configuration::get('MANDARINPAY_SECRET_KEY'))) {
            return null;
        }
        return $this->display(__FILE__, 'mandarinpay.tpl');
    }


    /**
     * Returns domain name according to configuration
     */
    public static function getShopDomain($http = false, $entities = false) {
        if (!($domain = Configuration::get('PS_SHOP_DOMAIN'))) {
            $domain = Tools::getHttpHost();
        }
        if ($entities) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }
        if ($http) {
            $domain = 'http://' . $domain;
        }
        return $domain;
    }

    /**
     * Get MandarinPay payment URL
     */
    public static function getMandarinpayServerUrl() {
        $serverUrl = Configuration::get('MANDARINPAY_SERVER_URL');
        if (empty($serverUrl)) {
            $serverUrl = self::DEFAULT_SERVER_URL;
        }
        return $serverUrl;
    }

    /**
     * Get currency ISO code by id
     */
    public function getCurrencyIsoCodeById($id) {
        $isoCode = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT `iso_code`
            FROM `' . _DB_PREFIX_ . 'currency`
            WHERE `deleted` = 0
                AND `id_currency` = "' . pSQL($id) . '"');
        return $isoCode;
    }


	


}