<?php
require_once('api/Simpla.php');

class MandarinBank extends Simpla
{
	public function checkout_form($order_id, $button_text = null)
	{
		if(empty($button_text))
			$button_text = 'Перейти к оплате';

		$order = $this->orders->get_order((int)$order_id);
		$payment_method = $this->payment->get_payment_method($order->payment_method_id);
		$payment_settings = $this->payment->get_payment_settings($payment_method->id);

		$amount = $this->money->convert($order->total_price, $payment_method->currency_id, false);

		$success_url = $this->config->root_url.'/order/'.$order->url;

		$fail_url = $this->config->root_url.'/order/'.$order->url;
		$fields = array();

		$sign  = $this->generate_form();//sha256($order->email + "-" + $payment_settings['merchantId'] + "-" + $order->id + "-" + $amount + "-" + $payment_settings['secret']);

		/*$button = "<form action='https://secure.mandarinpay.com/Pay' method='POST'>
					<input type='hidden' name='merchantId' value='812' />
					<input type='hidden' name='price' value='".$amount."' />
					<input type='hidden' name='orderId' value='".$order->id."' />
					<input type='hidden' name='customer_email' value='".$order->email."' />
					<input type='hidden' name='callbackUrl' value='".$success_url."' />
					<input type='hidden' name='returnUrl' value='".$success_url."' />
					<input type='hidden' name='sign' value='".$sign."' />
					<input type='submit' value='Оплатить' />
					</form>";*/
		$button = "<form action='https://secure.mandarinpay.com/Pay' method='POST'>".$this->generate_form($payment_settings['secret'], $values = array(
				"email" => $order->email,
				"merchantId" => $payment_settings['merchantId'],
				"orderId" => $order->id,
				"price" => $amount,
		))."<input type='submit' value='Оплатить' /></form>";
		return $button;
	}

	public function calc_sign($secret, $fields)
	{
		if(isset($fields['sign'])){
			unset($fields['sign']);
		}
		ksort($fields);
		$secret_t = '';
		foreach($fields as $key => $val)
		{
			$secret_t = $secret_t . '-' . $val;
		}
		$secret_t = substr($secret_t, 1) . '-' . $secret;
		return hash("sha256", $secret_t);
	}

	public function generate_form($secret, $fields)
	{
		$sign = $this->calc_sign($secret, $fields);
		$form = "";
		foreach($fields as $key => $val)
		{
			$form .= '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
		}
		$form .= '<input type="hidden" name="sign" value="'.$sign.'"/>';
		return $form;
	}


}