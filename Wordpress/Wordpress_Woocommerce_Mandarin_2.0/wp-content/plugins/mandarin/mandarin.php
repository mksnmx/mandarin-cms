<?php
/*
 * Plugin Name: Mandarin
 * Text Domain: mandarin-pay
 * Plugin URI: https://mandarin.io/
 * Description: Extends WooCommerce by Adding the Mandarin Gateway.
 * Version: 1.2
 * Author: MandarinLtd
*/

add_action( 'plugins_loaded', 'mandarin_pay_init', 0 );
function mandarin_pay_init() {

    error_log('mandarin_pay_init called');

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        error_log('WC_Payment_Gateway not found');
        return;
    }

    error_log('Creating Mandarin_Pay class');

	class Mandarin_Pay extends WC_Payment_Gateway {
		function __construct(){
            error_log('Mandarin_Pay constructor called');

            $this->id = "mandarin_pay";
            $this->method_title = __( "Mandarin", 'mandarin-pay' );
            $this->order_button_text  = __( 'Proceed to MandarinPay', 'mandarin-pay' );
            $this->method_description = __( "Mandarin Gateway Plug-in for WooCommerce", 'mandarin-pay' );
            $this->title = __( "Mandarin", 'mandarin-pay' );
            $this->icon = null;
            $this->has_fields = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->supports = array('products');

            foreach ( $this->settings as $setting_key => $value ) {
                $this->$setting_key = $value;
            }

            error_log('Mandarin settings loaded: enabled=' . $this->enabled . ', merchantId=' . $this->merchantId);

			add_action('woocommerce_thankyou', array($this, 'successful_thankyou'));
			add_action('valid-mandarin-pay-ipn-reques', array($this, 'successful_request') );
			add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action('woocommerce_api_mandarin_pay', array($this, 'check_ipn_response'));
		}

        public function is_available() {
            error_log('Mandarin is_available called');

            // Базовые проверки
            if (!$this->enabled || $this->enabled !== 'yes') {
                error_log('Mandarin not enabled');
                return false;
            }

            // Проверяем обязательные настройки
            if (empty($this->merchantId) || empty($this->secret)) {
                error_log('Mandarin credentials missing');
                return false;
            }

            error_log('Mandarin is available');
            return true;
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
				echo esc_html(wpautop(wptexturize($this->description)));
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
						$form = $form . '<input type="hidden" name="'.esc_attr($key).'" value="' . esc_attr(htmlspecialchars($val)) . '"/>'."\n";
				}
				$form = $form . '<input type="hidden" name="sign" value="'.esc_attr($sign).'"/>';
				return $form;
		}

        public function generate_form($order_id){
            global $woocommerce;
            error_log('=== MANDARIN GENERATE_FORM START ===');
            error_log('Generating form for order: ' . $order_id);

            $order = wc_get_order($order_id);

            $out_summ = number_format($order->get_total(), 2, '.', '');

            // Определяем URLs для callback и возврата
            if('' == get_option('permalink_structure')){
                $callback = site_url().'/?wc-api=mandarin_pay';
                $return = $this->get_return_url($order);
            }else{
                $callback = site_url().'/wc-api/mandarin_pay/';
                $return = $this->get_return_url($order);
            }

            // Генерируем скрытые поля формы с подписью
            $f = $this->generate_formpub($this->secret,array(
                'callbackUrl' => $callback,
                'merchantId' => $this->merchantId,
                'price' => $out_summ,
                'orderId' => $order_id,
                'customer_email' => $order->get_billing_email(),
                'returnUrl' => $return
            ));

            error_log('Mandarin form data prepared, callback: ' . $callback);
            error_log('=== MANDARIN GENERATE_FORM END ===');

            // Возвращаем красивую страницу с загрузчиком
            return $this->generate_payment_loader_page($f, $order);
        }

        /**
         * Создает красивую страницу загрузки с автоматическим перенаправлением
         * Это обеспечивает плавный пользовательский опыт вместо резкого перехода
         */
        private function generate_payment_loader_page($form_fields, $order) {
            return '
    <div id="mandarin-payment-container" style="
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        text-align: center;
        font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
    ">
        <!-- Спиннер загрузки -->
        <div id="mandarin-spinner" style="
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #0073aa;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        "></div>
        
        <!-- Сообщение для пользователя -->
        <h3 style="color: #333; margin: 0 0 10px 0;">
            '.__('Подготовка к оплате...', 'mandarin-pay').'
        </h3>
        
        <p style="color: #666; margin: 0 0 20px 0;">
            '.__('Пожалуйста, подождите. Мы перенаправляем вас на безопасную страницу оплаты.', 'mandarin-pay').'
        </p>
        
        <!-- Скрытая форма для автоматической отправки -->
        <form id="mandarin_form" action="https://secure.mandarinpay.com/Pay" method="POST" style="display: none;">
            '.$form_fields.'
        </form>
        
        <!-- Кнопка для ручной отправки, если автоматика не сработает -->
        <button id="manual-submit" onclick="document.getElementById(\'mandarin_form\').submit();" style="
            background: #0073aa;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: none;
        ">
            '.__('Перейти к оплате', 'mandarin-pay').'
        </button>
        
        <a href="'.$order->get_cancel_order_url().'" style="
            color: #666;
            text-decoration: none;
            margin-top: 15px;
            font-size: 13px;
        ">
            '.__('Отменить заказ', 'mandarin-pay').'
        </a>
    </div>

    <style>
        /* Анимация вращения спиннера */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Адаптивность для мобильных устройств */
        @media (max-width: 768px) {
            #mandarin-payment-container {
                padding: 20px;
                min-height: 300px;
            }
        }
    </style>

    <script>
        console.log("Mandarin payment loader initialized");
        
        // Автоматическая отправка формы через 2 секунды
        // Этот таймаут дает пользователю время увидеть, что происходит
        var submitTimer = setTimeout(function() {
            console.log("Auto-submitting Mandarin payment form");
            document.getElementById("mandarin_form").submit();
        }, 2000);
        
        // Показываем кнопку ручной отправки через 8 секунд
        // Это защитная мера на случай, если автоматика не сработает
        setTimeout(function() {
            document.getElementById("manual-submit").style.display = "block";
            document.querySelector("h3").innerHTML = "'.__('Готово к оплате', 'mandarin-pay').'";
            document.querySelector("p").innerHTML = "'.__('Нажмите кнопку ниже для перехода к оплате', 'mandarin-pay').'";
            
            // Останавливаем спиннер
            document.getElementById("mandarin-spinner").style.display = "none";
        }, 8000);
        
        // Обработка ошибок загрузки
        window.addEventListener("beforeunload", function() {
            clearTimeout(submitTimer);
        });
    </script>';
        }

        function process_payment($order_id){
            error_log('=== MANDARIN PROCESS_PAYMENT START ===');
            error_log('Mandarin process_payment called for order: ' . $order_id);

            // Используем современный API для получения заказа
            $order = wc_get_order($order_id);

            // Критически важно: устанавливаем статус "ожидание оплаты"
            $order->update_status('pending', __('Ожидание оплаты через Mandarin', 'mandarin-pay'));

            // Формируем URL для страницы оплаты
            $pay_url = $order->get_checkout_payment_url(true);
            error_log('Mandarin redirect URL: ' . $pay_url);
            error_log('=== MANDARIN PROCESS_PAYMENT END ===');

            return array(
                'result' => 'success',
                'redirect' => $pay_url
            );
        }

        function receipt_page($order){
            error_log('=== MANDARIN RECEIPT_PAGE START ===');
            error_log('Mandarin receipt_page called for order: ' . $order);

            echo wp_kses_post('<p>'.__('Спасибо за Ваш заказ, пожалуйста, нажмите кнопку ниже, чтобы заплатить.', 'mandarin-pay').'</p>');
            echo $this->generate_form($order);

            error_log('=== MANDARIN RECEIPT_PAGE END ===');
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
                                            //case 'callbackUrl':
                                            //case 'returnUrl':
                                            //    $hash_arr[$key] = esc_url($h_var);
                                            //    break;
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
					$order->update_status('failed', __('Платеж не оплачен', 'mandarin-pay'));
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
			$order->add_order_note(__('Платеж успешно завершен.', 'mandarin-pay'));
			$order->update_status('completed', __('Платеж успешно оплачен', 'mandarin-pay'));
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
            $gateways[] = 'Mandarin_Pay';
            return $gateways;
        }

	}
	
	add_filter( 'woocommerce_payment_gateways', 'add_mandarin_pay_gateway' );
    function add_mandarin_pay_gateway( $methods ) {
        error_log('add_mandarin_pay_gateway called, methods count: ' . count($methods));
        $methods[] = 'Mandarin_Pay';
        error_log('add_mandarin_pay_gateway after adding: ' . count($methods));
        error_log('Available gateways: ' . implode(', ', $methods));
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

add_action('woocommerce_blocks_loaded', function() {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        require_once __DIR__ . '/class-mandarin-blocks-support.php';  // Создай новый файл
        add_action('woocommerce_blocks_payment_method_type_registration', function($registry) {
            $registry->register(new Mandarin_Blocks_Support());
        });
    }
});
