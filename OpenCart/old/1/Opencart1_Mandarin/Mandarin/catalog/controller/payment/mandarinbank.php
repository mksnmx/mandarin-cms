<?php
class ControllerPaymentMandarinbank extends Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->data['form'] = $this->generate_form($this->config->get('mandarinbank_signature'), $values = array(
            "email" => $order_info['email'],
            "merchantId" => $this->config->get('mandarinbank_merchant'),
            "orderId" => $this->session->data['order_id'],
            "price" => round($order_info['total'], 2),
        ));

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mandarinbank.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/mandarinbank.tpl';
		} else {
			$this->template = 'default/template/payment/mandarinbank.tpl';
		}	

		$this->render();
	}

	public function check_sign($secret,$fields)
	{
		$signAnswer = $fields['sign'];
		$sign = $this->calc_sign($secret,$fields);
		return $sign == $signAnswer;
	}

	public function calc_sign($secret, $fields)
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

	public function generate_form($secret, $fields)
	{
		$sign = $this->calc_sign($secret, $fields);
		$form = "";
		foreach ($fields as $key => $val) {
			$form .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($val) . '"/>' . "\n";
		}
		$form .= '<input type="hidden" name="sign" value="' . $sign . '"/>';
		return $form;
	}

	public function callback() {
        $amount = $_POST['price'];

        $order_id = trim($_POST['orderId']);

        $marchantId = $_POST['merchantId'];

        $manderinBankTransactionId = $_POST['transaction'];

        $customerEmail = $_POST['customer_email'];
        $customerPhone = $_POST['customer_phone'];
        $action = $_POST['action'];

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($order_id);
        if (empty($order))
            die('Оплачиваемый заказ не найден');

        $status = 'failed';
        if (isset($_POST['status'])) {
            $status = $_POST['status'];
        }


        if ($status == 'failed') {
            die("Оплата прошла с ошибкой (status faild)");
        }

        if (round($order['total'], 2) != round($amount, 2) || $amount <= 0) {
            die("Неверная сумма оплаты");
        }

        if ($this->config->get('mandarinbank_merchant') != $marchantId) {
            die('Мерчант указан не верно');
        }


		if (!$this->check_sign($this->config->get('mandarinbank_signature'),$_POST)) {
			die('Sign указан не верно');
		}

        $this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
	}
}
?>