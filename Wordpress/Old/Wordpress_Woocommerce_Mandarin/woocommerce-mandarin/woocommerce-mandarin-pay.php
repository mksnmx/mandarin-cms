<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Mandarin Gateway
 *
 * Provides Mandarin Payment Gateway.
 *
 * @class 		Mandarin_Pay
 * @extends		WC_Payment_Gateway
 * @version		2.1.0
 * @package		plugins/woocommerce-mandarin-pay/
 * @author 		Rackwheel
 */
class Mandarin_Pay extends WC_Payment_Gateway {
	// Setup our Gateway's id, description and other values
	function __construct() {
		
		// The global ID for this Payment method
		$this->id = "Mandarin_Pay";
	
		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = __( "Mandarin", 'mandarin-pay' );
		
		$this->order_button_text  = __( 'Proceed to MandarinPay', 'mandarin-pay' );
	
		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = __( "Mandarin Gateway Plug-in for WooCommerce", 'mandarin-pay' );
	
		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = __( "Mandarin", 'mandarin-pay' );
	
		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = null;
	
		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = false;
	
		// Supports the default credit card form
		/*$this->supports = array( 'default_credit_card_form' );*/
	
		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();
	
		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		// Lets check for SSL
		/*add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );*/
		
		// Save settings
		/*if ( is_admin() ) {*/
			// Actions
			add_action('valid-mandarin-pay-ipn-reques', array($this, 'successful_request') );
			add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			// Payment listener/API hook
			add_action('woocommerce_api_mandarin_pay', array($this, 'check_ipn_response'));
		/*}		*/
	} // End __construct()
	
	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Включить / Выключить', 'mandarin-pay' ),
				'label'		=> __( 'Включить данную систему', 'mandarin-pay' ),
				'type'		=> 'checkbox',
				'default'	=> 'yes',
			),
			'title' => array(
				'title'		=> __( 'Заголовок', 'mandarin-pay' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Заголовок видимый покупателем при заказе', 'mandarin-pay' ),
				'default'	=> __( 'Mandarin', 'mandarin-pay' ),
			),
			'description' => array(
				'title'		=> __( 'Описание', 'mandarin-pay' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Описание, которое будет видно пользователю при заказе.', 'mandarin-pay' ),
				'default'	=> __( 'Оплачивайте при помощи системы Mandarin.', 'mandarin-pay' ),
				'css'		=> 'max-width:350px;'
			),
			'merchantId' => array(
				'title'		=> __( 'MerchantId', 'mandarin-pay' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Дается системой Mandarin для доступа к системе', 'mandarin-pay' ),
			),
			'secret' => array(
				'title'		=> __( 'Секретный ключ', 'mandarin-pay' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Дается системой Mandarin для генерации проверочного кода', 'mandarin-pay' ),
			)
		);		
	}
	
	/**
	* There are no payment fields for sprypay, but we want to show the description if set.
	**/
	function payment_fields(){
		if ($this->description){
			echo wpautop(wptexturize($this->description));
		}
	}
	/**
	* Generate the dibs button link
	**/
	public function generate_form($order_id){
		global $woocommerce;

		$order = new WC_Order( $order_id );

         /*if(get_option('woocommerce_currency')=='RUB')
	         $currency='rur';
         else
        	 $currency=strtolower(get_option('woocommerce_currency'));*/
		$out_summ = number_format($order->order_total, 2, '.', '');

		$args = array(
				// Form
				'merchantId' => $this->merchantId,
				'price' => $out_summ,
				'orderId' => $order_id,
                'email'=> $order->billing_email,
				'sign' => hash('sha256',$order->billing_email."-".$this->merchantId."-".$order_id."-".$out_summ."-".$this->secret),
		);
		/*error_log(print_r($args,true),3, "my-errors.log");*/

		$mandarin_args = apply_filters('woocommerce_mandarin_args', $args);
		/*error_log(print_r($mandarin_args,true),3, "my-errors.log");*/

		$args_array = array();

		foreach ($args as $key => $value){
			$args_array[] = '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'" />';
		}
		/*error_log(print_r($args_array,true),3, "my-errors.log");*/

		return
			'<form action="https://secure.mandarinpay.com/Pay" method="POST">'."\n".
			implode("\n", $args_array).
			'<input type="submit" class="button alt" value="'.__('Оплатить через MandarinPay', 'woocommerce').'" />
			 <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Отказаться от оплаты & вернуться в корзину', 'woocommerce').'</a>'."\n".
			'</form>';
	}
	
	/**
	 * Process the payment and return the result
	 **/
	function process_payment($order_id){
		$order = new WC_Order($order_id);
		return array(
			'result' => 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
		);
	}

	/**
	* receipt_page
	**/
	function receipt_page($order){
		echo '<p>'.__('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'woocommerce').'</p>';
		/*error_log('Generating form',3, "my-errors.log");*/
		echo $this->generate_form($order);
	}


	/**
	* Check Response
	**/
	function check_ipn_response(){
		global $woocommerce;
		
		/*var_dump($_POST);
		exit();*/
        /*if(get_option('woocommerce_currency')=='RUB')
	         $currency='rur';
         else
        	 $currency=strtolower(get_option('woocommerce_currency'));*/
			 /*error_log('starting proceed',3, "my-errors.log");
			 error_log(print_r($_POST,true),3, "my-errors.log");*/
		if (isset($_POST['status']) && $_POST['status'] == 'success'){
			/*error_log('success signal',3, "my-errors.log");*/
			$hash_arr = array();
			foreach($_POST as $key => $h_var){
				if($key != 'sign'){
					$hash_arr[$key] = $h_var;
				}
			}
			ksort($hash_arr);
			$hash = hash('sha256',implode('-',$hash_arr)."-".$this->secret);
			if($hash == $_POST['sign']){
				/*$order_id = $_POST['order_id'];
				$order = new WC_Order($order_id);*/
				/*error_log(print_r($_POST,true),3, "my-errors.log");*/
				echo('ok');
				/*error_log('making order ok',3, "my-errors.log");*/
				do_action('valid-mandarin-pay-ipn-reques', $_POST);
			} else {
				/*error_log(print_r($_POST,true),3, "my-errors.log");*/
				echo('error Signature failed');
				/*error_log('signature failed',3, "my-errors.log");**/
				exit();
			}
		}
		else {
			if (isset($_POST['status'])){
				/*error_log('not successful',3, "my-errors.log");*/
				/*error_log(print_r($_POST,true),3, "my-errors.log");*/
				$inv_id = $_POST['orderId'];
				$order = new WC_Order($inv_id);
				$order->update_status('failed', __('Платеж не оплачен', 'woocommerce'));
				wp_redirect($order->get_cancel_order_url());
				exit;
			}
		}
	}

	/**
	* Successful Payment!
	**/
	function successful_request($posted){
		global $woocommerce;

		$inv_id = $posted['orderId'];
		$order = new WC_Order($inv_id);

		// Check order not already completed
		if ($order->status == 'completed'){
			exit;
		}
		/*error_log('successfully changed',3, "my-errors.log");*/
		// Payment completed
		$order->add_order_note(__('Платеж успешно завершен.', 'woocommerce'));
		$order->payment_complete();
		$order->update_status('on-hold', __('Платеж успешно оплачен', 'woocommerce'));
		$woocommerce->cart->empty_cart();
		exit;
	}

}