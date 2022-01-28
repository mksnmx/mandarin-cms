INSERT INTO `#__jshopping_payment_method` (`name_en-GB`, `name_de-DE`, `payment_code`, `payment_class`, `scriptname`, `payment_publish`, `payment_ordering`, `payment_params`, `payment_type`, `price`, `price_type`, `tax_id`, `show_descr_in_email`) VALUES
('2CheckOut',  '2CheckOut',  '2checkout', 'pm_2checkout' , 'pm_2checkout', 1, 3, 'test=0\nseller_id=90XXXXX\nsecret_word=XXXXXXXXXXX\ntransaction_end_status=7\ntransaction_pending_status=1\ntransaction_failed_status=3\n\n', 2, 0.00, 1, 1, 0);
UPDATE `#__jshopping_payment_method` SET `image` = '/components/com_jshopping/payments/pm_2checkout/pay_logo/logo-2checkout.png' WHERE `payment_class` = 'pm_2checkout';



