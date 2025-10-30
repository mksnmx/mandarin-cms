<?php
/*
 * Plugin Name: Mandarin v3
 * Text Domain: mandarin-pay-v3
 * Plugin URI: https://mandarin.io/
 * Description: Extends WooCommerce by Adding the Mandarin Gateway. v3
 * Version: 3.0
 * Author: MandarinLtd
*/

add_action( 'plugins_loaded', 'mandarin_pay_v3_init', 0 );
function mandarin_pay_v3_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

	class Mandarin_Pay_v3 extends WC_Payment_Gateway {
		public $merchantId;
		public $secret;
		function __construct(){
            $this->id = "mandarin_pay_v3";
            $this->method_title = __( "Mandarin v3", 'mandarin-pay-v3' );
            $this->order_button_text  = __( 'Proceed to MandarinPay', 'mandarin-pay-v3' );
            $this->method_description = __( "Mandarin Gateway Plug-in for WooCommerce v3", 'mandarin-pay-v3' );
            $this->title = __( "Mandarin v3", 'mandarin-pay-v3' );
            $this->icon = null;
            $this->has_fields = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->supports = array('products');

            foreach ( $this->settings as $setting_key => $value ) {
                $this->$setting_key = $value;
            }

			add_action('woocommerce_thankyou', array($this, 'successful_thankyou'));
			add_action('valid-mandarin-pay-v3-ipn-request', array($this, 'successful_request') );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action('woocommerce_api_mandarin_pay_v3', array($this, 'check_ipn_response'));
		}

        public function is_available() {
            if ('yes' !== $this->enabled) {
                return false;
            }
            if (empty($this->merchantId) || empty($this->secret)) {
                return false;
            }
            return true;
        }
		
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'		=> __( 'Включить / Выключить', 'mandarin-pay-v3' ),
					'label'		=> __( 'Включить данную систему', 'mandarin-pay-v3' ),
					'type'		=> 'checkbox',
					'default'	=> 'yes',
				),
				'title' => array(
					'title'		=> __( 'Заголовок', 'mandarin-pay-v3' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Заголовок видимый покупателем при заказе', 'mandarin-pay-v3' ),
					'default'	=> __( 'Mandarin v3', 'mandarin-pay-v3' ),
				),
				'description' => array(
					'title'		=> __( 'Описание', 'mandarin-pay-v3' ),
					'type'		=> 'textarea',
					'desc_tip'	=> __( 'Описание, которое будет видно пользователю при заказе.', 'mandarin-pay-v3' ),
					'default'	=> __( 'Вы перейдете в шлюз оплаты сервиса MANDARINBANK, где Вам будет предложено оплатить заказ любым удобным способом: картами Visa, MasterCard, Яндекс-Деньги, Webmoney, терминалы QIWI', 'mandarin-pay-v3' ),
					'css'		=> 'max-width:350px;'
				),
				'merchantId' => array(
					'title'		=> __( 'Merchant ID', 'mandarin-pay-v3' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Дается системой Mandarin для доступа к системе', 'mandarin-pay-v3' ),
				),
				'secret' => array(
					'title'		=> __( 'Merchant Secret', 'mandarin-pay-v3' ),
					'type'		=> 'text',
					'desc_tip'	=> __( 'Дается системой Mandarin для генерации проверочного кода', 'mandarin-pay-v3' ),
				)
			);		
		}
		
		function payment_fields(){
			if ($this->description){
				echo esc_html(wpautop(wptexturize($this->description)));
			}
		}

        function process_payment($order_id){
            $order = wc_get_order($order_id);
            $order->update_status('pending', __('Ожидание оплаты через Mandarin', 'mandarin-pay-v3'));

            $apiUrl = 'https://secure.mandarinpay.com/api/transactions';
            $out_summ = number_format($order->get_total(), 2, '.', '');

            if('' == get_option('permalink_structure')){
                $callback = site_url().'/?wc-api=mandarin_pay_v3';
                $return = $this->get_return_url($order);
            }else{
                $callback = site_url().'/wc-api/mandarin_pay_v3/';
                $return = $this->get_return_url($order);
            }

            $request_data = [
                'payment' => [
                    'action'  => 'pay',
                    'orderId' => strval($order_id),
                    'price'   => $out_summ
                ],
                'customerInfo' => [
                    'email' => $order->get_billing_email(),
                ],
                'urls' => [
                    'callback' => $callback,
                    'return'   => $return
                ]
            ];

            $phone = $order->get_billing_phone();
            if (!empty($phone)) {
                $request_data['customerInfo']['phone'] = $phone;
            }

            $request_body = json_encode($request_data);
            $secret = html_entity_decode($this->secret);
            $auth_string = base64_encode($this->merchantId . ':' . $secret);

            $response = wp_remote_post($apiUrl, [
                'method'    => 'POST',
                'headers'   => [
                    'Authorization' => 'Basic ' . $auth_string,
                    'Content-Type'  => 'application/json',
                    'MID'           => $this->merchantId
                ],
                'body'      => $request_body,
                'timeout'   => 45,
            ]);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log('Mandarin API request failed: ' . $error_message);
                wc_add_notice('Payment error: ' . $error_message, 'error');
                return ['result' => 'failure'];
            }

            $response_body = wp_remote_retrieve_body($response);
            $decoded_body = json_decode($response_body, true);

            if (wp_remote_retrieve_response_code($response) == 200 && isset($decoded_body['userWebLink'])) {
                error_log('Mandarin payment created. Redirecting to: ' . $decoded_body['userWebLink']);
                return [
                    'result'   => 'success',
                    'redirect' => $decoded_body['userWebLink'],
                ];
            } else {
                $error_message = isset($decoded_body['error']) ? $decoded_body['error'] : 'Unknown error.';
                error_log('Mandarin API error: ' . $error_message);
                wc_add_notice('Payment error: ' . $error_message, 'error');
                return ['result' => 'failure'];
            }
        }
        
		public function calc_sign($secret, $fields)
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
		
		function check_ipn_response(){
            $raw_post_data = file_get_contents('php://input');
            error_log('Mandarin IPN received: ' . $raw_post_data);

            $callback_data = json_decode($raw_post_data, true);

            if (is_null($callback_data) || !isset($callback_data['status'])) {
                error_log('Mandarin IPN: Invalid JSON or status not set.');
                status_header(400);
                exit('Invalid request');
            }

            // Проверяем подпись
            if (isset($callback_data['sign'])) {
                $received_sign = $callback_data['sign'];
                unset($callback_data['sign']);
                $secret = html_entity_decode($this->secret);
                $calculated_sign = $this->calc_sign($secret, $callback_data);

                if ($received_sign !== $calculated_sign) {
                    error_log('Mandarin IPN: Signature validation failed.');
                    status_header(400);
                    exit('Signature failed');
                }
            } else {
                 error_log('Mandarin IPN: No signature found in callback.');
                 status_header(400);
                 exit('No signature');
            }
            
            $order_id = sanitize_key($callback_data['orderId']);
            $order = wc_get_order($order_id);

            if (!$order) {
                error_log('Mandarin IPN: Order not found for id: ' . $order_id);
                status_header(404);
                exit('Order not found');
            }

            if ($callback_data['status'] == 'success') {
                do_action('valid-mandarin-pay-v3-ipn-request', $callback_data);
                echo('ok');
                exit();
            } else {
                $order->update_status('failed', __('Платеж не удался или был отменен.', 'mandarin-pay-v3'));
                error_log('Mandarin IPN: Payment failed for order ' . $order_id . '. Status: ' . $callback_data['status']);
                echo('ok'); // Говорим "ok" даже при ошибке, чтобы Mandarin не повторял запросы
                exit();
            }
		}
		
		function successful_request($posted){
			global $woocommerce;
			$order_id = sanitize_key($posted['orderId']);
			$order = wc_get_order($order_id);
			
			if (!$order || $order->get_status() == 'completed'){
                exit;
			}
			
			$woocommerce->cart->empty_cart();
			$order->add_order_note(__('Платеж успешно завершен.', 'mandarin-pay-v3'));
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

        public function add_gateway_class($gateways) {
            $gateways[] = 'Mandarin_Pay_v3';
            return $gateways;
        }

	}
	
	add_filter( 'woocommerce_payment_gateways', 'add_mandarin_pay_v3_gateway' );
    function add_mandarin_pay_v3_gateway( $methods ) {
        $methods[] = 'Mandarin_Pay_v3';
        return $methods;
    }
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mandarin_pay_v3_action_links' );
function mandarin_pay_v3_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mandarin_pay_v3' ) . '">' . __( 'Настроить', 'mandarin-pay-v3' ) . '</a>',
	);
	
	return array_merge( $plugin_links, $links );
}

add_action('woocommerce_blocks_loaded', function() {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        require_once __DIR__ . '/class-mandarin-blocks-support.php';
        add_action('woocommerce_blocks_payment_method_type_registration', function($registry) {
            $registry->register(new Mandarin_Blocks_Support_v3());
        });
    }
});
