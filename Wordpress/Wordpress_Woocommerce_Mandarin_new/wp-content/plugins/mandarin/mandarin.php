<?php
/*
Plugin Name: MandarinPay
Plugin URI: http://www.mandarinbank.com/
Description: Extends WooCommerce by Adding the MandarinPay Gateway.
Version: 1.0
Author: MandarinLtd
*/

add_action( 'plugins_loaded', 'mandarin_pay_init', 0 );
function mandarin_pay_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	//if(!defined('ABSPATH'))exit;

	class Mandarin_Pay extends WC_Payment_Gateway {
		function __construct(){
			$this->id = "Mandarin_Pay";
			$this->method_title = __( "Mandarin", 'mandarin-pay' );
			$this->order_button_text  = __( 'Proceed to MandarinPay', 'mandarin-pay' );
			$this->method_description = __( "Mandarin Gateway Plug-in for WooCommerce", 'mandarin-pay' );
			$this->title = __( "Mandarin", 'mandarin-pay' );
			$this->icon = null;
			$this->has_fields = false;
			$this->init_form_fields();
			$this->init_settings();
			
			foreach ( $this->settings as $setting_key => $value ) {
				$this->$setting_key = $value;
			}
			add_action('woocommerce_thankyou', array($this, 'successful_thankyou'));
			add_action('valid-mandarin-pay-ipn-reques', array($this, 'successful_request') );
			add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action('woocommerce_api_mandarin_pay', array($this, 'check_ipn_response'));
		}
		
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
					'default'	=> __( 'Вы перейдете в шлюз оплаты сервиса MANDARINBANK, где Вам будет предложено оплатить заказ любым удобным способом: картами Visa, MasterCard, Яндекс-Деньги, Webmoney, терминалы QIWI', 'mandarin-pay' ),
					'css'		=> 'max-width:350px;'
				),
				'merchantId' => array(
					'title'		=> __( 'Merchant ID', 'mandarin-pay' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Дается системой Mandarin для доступа к системе', 'mandarin-pay' ),
				),
				'secret' => array(
					'title'		=> __( 'Merchant Secret', 'mandarin-pay' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Дается системой Mandarin для генерации проверочного кода', 'mandarin-pay' ),
				)
			);		
		}
		
		function payment_fields(){
			if ($this->description){
				echo wpautop(wptexturize($this->description));
			}
		}
		
		function calc_sign($secret, $fields)
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

		function generate_formpub($secret, $fields)
		{
				$sign = $this->calc_sign($secret, $fields);
				$form = "";
				foreach($fields as $key => $val)
				{
						$form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
				}
				$form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
				return $form;
		}
		
		public function generate_form($order_id){
			global $woocommerce;

			$order = new WC_Order( $order_id );
			
			$out_summ = number_format($order->order_total, 2, '.', '');
                        
                        if('' == get_option('permalink_structure')){
                            $callback = site_url().'/?wc-api=mandarin_pay';
                            $return = $this->get_return_url( $order );
                        }else{
                            $callback = site_url().'/wc-api/mandarin_pay/';
                            $return = $this->get_return_url( $order );
                        }
                        
			$f = $this->generate_formpub($this->secret,array(
                                        'callbackUrl' => $callback,
					'merchantId' => $this->merchantId,
					'price' => $out_summ,
					'orderId' => $order_id,
					'customer_email' => $order->billing_email,
                                        'returnUrl' => $return
				)
			);
			

			return
				'<form action="https://secure.mandarinpay.com/Pay" method="POST">'."\n".
				$f.
				'<input type="submit" class="button alt" value="'.__('Оплатить через MandarinPay', 'woocommerce').'" />
				 <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Отказаться от оплаты & вернуться в корзину', 'woocommerce').'</a>'."\n".
				'</form>';
		}
		
		function process_payment($order_id){
			$order = new WC_Order($order_id);
			return array(
				'result' => 'success',
				'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
			);
		}
		
		function receipt_page($order){
			echo '<p>'.__('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'woocommerce').'</p>';
			echo $this->generate_form($order);
		}
		
		function check_ipn_response(){
                    
			if (isset($_POST['status']) && $_POST['status'] == 'success'){
				$hash_arr = array();
                                //Test
                                //file_put_contents('./log.txt', json_encode($_POST));
                                // End Test
				foreach($_POST as $key => $h_var){
					if($key != 'sign'){
                                            switch ($key) {
                                            case 'email':
                                            case 'customer_email':
                                                $hash_arr[$key] = sanitize_email($h_var);
                                                break;
                                            case 'callbackUrl':
                                            case 'returnUrl':
                                                $hash_arr[$key] = esc_url($h_var);
                                                break;
                                            case 'orderId':
                                            case 'merchantId':
                                                $hash_arr[$key] = sanitize_key($h_var);
                                                break;
                                            default:
                                                if ($h_var == '  ') {
                                                  $hash_arr[$key] = '  ';
                                                } else {
                                                  $hash_arr[$key] = sanitize_text_field($h_var);
                                                }
                                            }
					}
				}
                                //Test
                                //file_put_contents('./log2.txt', json_encode($hash_arr));
                                // End Test
				ksort($hash_arr);
				$hash = hash('sha256',implode('-',$hash_arr)."-".$this->secret);
				if($hash == $_POST['sign']){
					echo('ok');
					do_action('valid-mandarin-pay-ipn-reques', $_POST);
				} else {
					echo('error Signature failed');
					
					exit();
				}
			}
			else {
				if (isset($_POST['status'])){
					$inv_id = sanitize_key($_POST['orderId']);
					$order = new WC_Order($inv_id);
					$order->update_status('failed', __('Платеж не оплачен', 'woocommerce'));
					wp_redirect($order->get_cancel_order_url());
					exit;
				}
			}
		}
		
		function successful_request($posted){
			global $woocommerce;
			$inv_id = $posted['orderId'];
			$order = new WC_Order($inv_id);
			
			if ($order->status == 'completed'){
                            exit;
			}
			$woocommerce->cart->empty_cart();
			$order->add_order_note(__('Платеж успешно завершен.', 'woocommerce'));
			$order->update_status('completed', __('Платеж успешно оплачен', 'woocommerce'));
			$order->payment_complete();
                        		
			exit;
		}
                function successful_thankyou( $order_id ){
			global $woocommerce;
                        
                        if ( ! $order_id ) return;
                        
			$order = new WC_Order($order_id);
                        
			if ($order->status == 'completed'){
                            $woocommerce->cart->empty_cart();
			}
                        
		}

	}
	
	add_filter( 'woocommerce_payment_gateways', 'add_mandarin_pay_gateway' );
	function add_mandarin_pay_gateway( $methods ) {
		$methods[] = 'Mandarin_Pay';
		return $methods;
	}
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mandarin_pay_action_links' );
function mandarin_pay_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mandarin_pay' ) . '">' . __( 'Настроить', 'mandarin-pay' ) . '</a>',
	);
	
	return array_merge( $plugin_links, $links );
}