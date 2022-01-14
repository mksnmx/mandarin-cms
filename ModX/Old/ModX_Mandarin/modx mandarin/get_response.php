<?php
if(isset($_SESSION['mandarin_values'])){
$args = array(
				// Form
				'merchantId' => $_SESSION['merchantId'],
				'price' => $_SESSION['total'],
				'orderId' => $_SESSION['orderId'],
                'email'=> $_SESSION['email'],
				'sign' => $_SESSION['sign']
		);

		foreach ($args as $key => $value){
			$args_array[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}

		return
			'<form action="https://secure.mandarinpay.com/Pay" method="POST">'."\n".
			implode("\n", $args_array).
			'<input type="submit" class="button alt" value="Оплата через MandarinPay" />'."\n".
			'</form>';
}