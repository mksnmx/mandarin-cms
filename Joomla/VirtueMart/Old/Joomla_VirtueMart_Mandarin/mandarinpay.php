<?php
/**
* @version      1.0.2
* @author       Lif.org.ua
* @package      VirtueMart
* @subpackage 	payment
* @copyright    Copyright (C) 2016 MandarinPay. All rights reserved.
* @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*/

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

if (!class_exists('vmPSPlugin'))
        require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

class plgVmPaymentMandarinpay extends vmPSPlugin {
        // instance of class
        public static $_this = false;

        function __construct(& $subject, $config) {
                parent::__construct($subject, $config);
                /**
                * Here we should assign data for two payment tables to work with. Some additional initializations can be done.
                */
                $jlang = JFactory::getLanguage ();
                $jlang->load ('plg_vmpayment_mandarinpay', JPATH_ADMINISTRATOR, NULL, TRUE);
                $this->_loggable = TRUE;
                $this->_debug = TRUE;
				/**
                 * assign columns for mandarinpay payment plugin table #_virtuemart_payment_plg_mandarinpay
                 */
                $this->tableFields = array_keys($this->getTableSQLFields());
				$this->_tablepkey = 'id'; //virtuemart_MANDARINPAY_id';
				$this->_tableId = 'id'; //'virtuemart_MANDARINPAY_id';
                //assign payment parameters from plugin configuration to paymentmethod table #_virtuemart_paymentmethods (payment_params column)
                $varsToPush = $this->getVarsToPush ();
                $this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
                
            }
        //===============================================================================
        // BACKEND
        /**
        * Functions to initialize parameters from configuration 
        * to be saved in payment table #_virtuemart_paymentmethods (payment_params field)
        * 
        * 
        * @param type $name
        * @param type $id
        * @param type $data
        * @return type
        */
        function plgVmDeclarePluginParamsPayment($name, $id, &$data) {
                return $this->declarePluginParams('payment', $name, $id, $data);
            }
        function plgVmDeclarePluginParamsPaymentVM3( &$data) {
            return $this->declarePluginParams('payment', $data);
            }

        function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
                return $this->setOnTablePluginParams($name, $id, $table);
            }
        //===========================================================================================================================
        //BACKEND
        /**
        * Create the table for this plugin if it does not yet exist.
        * @author Valerie Isaksen
        */
        protected function getVmPluginCreateTableSQL() {
                return $this->createTableSQL('Payment MandarinPay Table');
            }

        /**
        * Fields to create the payment table
        * @return string SQL Fileds
        */
        function getTableSQLFields() {
                $SQLfields = array(
                        'id' => 'bigint(1) unsigned NOT NULL AUTO_INCREMENT',
                        'virtuemart_order_id' => 'int(11) UNSIGNED ',
                        'order_number' => 'char(64)',
                        'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED ',
                        'payment_name' => 'char(255) NOT NULL DEFAULT \'\' ',
                        'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\' ',
                        'user_session' => 'varchar(255)', 
                );
                return $SQLfields;
            }
        /**
         * Create the table for this plugin if it does not yet exist.
         * This functions checks if the called plugin is active one.
         * When yes it is calling the standard method to create the tables
         * @author Valerie Isaksen
		 *
         * We must reimplement this trigger for joomla 1.7
         *
        */
        function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
                return $this->onStoreInstallPluginTable($jplugin_id);
            }
    
        //============================================================================================================================================
        //FRONTEND
        /**
         * This method is called after payer set confirm purchase in check out. 
         * It loads MandarinPay payment frame.
         */
        function plgVmConfirmedOrder($cart, $order) {
                if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
                        return null; // Another method was selected, do nothing
                    }
                if (!$this->selectedThisElement($method->payment_element)) {
                        return false;
                    }
                if (!class_exists ('VirtueMartModelOrders')) {
                        require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
                    }
                $session = JFactory::getSession ();
                $return_context = $session->getId ();
                $lang = JFactory::getLanguage();
                $filename = 'com_virtuemart';
                $lang->load($filename, JPATH_ADMINISTRATOR);

                $totalInPaymentAmount = round($order['details']['BT']->order_total, 2);
				if ($totalInPaymentAmount<= 0) {
                        vmInfo (vmText::_ ('VMPAYMENT_MANDARINPAY_PAYMENT_AMOUNT_INCORRECT'));
                        return FALSE;
                    }
                /**
                 * Prepare url parameters for payment frame.
                 */
                $trx_id = $order['details']['BT']->virtuemart_order_id;
				$merchantId = $method->merchant_id;
				$email = $order['details']['BT']->email;
				$sign = hash('sha256',$email."-".$merchantId."-".$trx_id."-".$totalInPaymentAmount."-".$method->secret_key);
                        
                /**
                 * Prepare data that should be stored in the database for arsenalpay payment method.
                 */
                $dbValues['user_session'] = $return_context;
                $dbValues['order_number'] = $order['details']['BT']->order_number;
                $dbValues['payment_name'] = $this->renderPluginName ($method, $order);
                $dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
                $dbValues['payment_order_total'] = $totalInPaymentAmount;
                $this->storePSPluginInternalData($dbValues); // save prepared data to arsenalpay database
                
                //=================================================================================
                /**
                 * The code for setting an iframe.
                 */
                $html = '<form action="https://secure.mandarinpay.com/Pay" method="POST">'."\n".
						'<input type="hidden" name="merchantId" value="'.$merchantId.'" />'.
						'<input type="hidden" name="price" value="'.$totalInPaymentAmount.'" />'.
						'<input type="hidden" name="orderId" value="'.$trx_id.'" />'.
						'<input type="hidden" name="email" value="'.$email.'" />'.
						'<input type="hidden" name="sign" value="'.$sign.'" />'.
						'<input type="submit" class="button alt" value="'.vmText::_ ('VMPAYMENT_MANDARINPAY_BUTOON').'" />
						 <a class="button cancel" href="">'.vmText::_ ('VMPAYMENT_MANDARINPAY_BUTOON_RETURN').'</a>'."\n".
						'</form>';

                /**
                 * Here we assign the pending status (from ArsenalPay configs) while the response will not be received back to the merchant site.
                 */
                $modelOrder = VmModel::getModel ('orders');
                $order['order_status'] = $method->status_pending;
                $order['customer_notified'] = 1;
                $order['comments'] = vmText::sprintf ('VMPAYMENT_MANDARINPAY_PAYMENT_STATUS_WAITING', $order_number);
                $modelOrder->updateStatusForOneOrder ($order['details']['BT']->virtuemart_order_id, $order, TRUE);
                /**
                 * Do nothing while the order will not be confirmed.
                 */
                $cart->_confirmDone = FALSE;
                $cart->_dataValidated = FALSE;
                $cart->setCartIntoSession (); 
                vRequest::setVar ('html', $html);
                return TRUE;
            }
        //========================================================================================
        //********************  Here are methods used in processing a callback  ***************//
        //========================================================================================
        function plgVmOnPaymentNotification () {
		
            if (!class_exists ('VirtueMartCart')) {
                    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
                }
            if (!class_exists ('shopFunctionsF')) {
                    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
                }
            if (!class_exists ('VirtueMartModelOrders')) {
                    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
                }
            $callback_msg = VRequest::getPost();
            if (!($virtuemart_order_id = $callback_msg['orderId'])) {
                    $this->exitf('ERR order');
                }
			
            if (!($paymentTable = $this->getDataByOrderId ($virtuemart_order_id))) {				
                if ( $callback_msg['status']!='success' ) { 
                        $this->exitf( 'NO' );
                        }
                // JError::raiseWarning(500, $db->getErrorMsg());
                $this->exitf('ERR_ACCOUNT');
                }
            elseif ( $callback_msg['status']=='success' ) {
				if (!($method = $this->getVmPluginMethod($paymentTable->virtuemart_paymentmethod_id))) {
                    $this->exitf('ERR method');
                } // Another method was selected, do nothing
				$hash_arr = array();
				$str = '';
				foreach($callback_msg as $key => $h_var){
					if($key != 'sign'){
						$hash_arr[$key] = $h_var;
					}
				}
				ksort($hash_arr);
				$hash = hash('sha256',implode('-',$hash_arr)."-".$method->secret_key);
				if($callback_msg['sign']==$hash){
					/*$this->exitf('YES');*/
				} else {
					$this->exitf( 'ERR_INVALID_SIGN');
				}
			}	
            //=======================================================================================================================
            /** Here we get preload data from arsenalpay payment table that was stored by method plgVmConfirmedOrder
            * and just prepare to save it in the renewed table with the response data in case it will be needed for some reason.
            * Without this block in the renewed table after response all the preload data will be nulled.
            //========================================================================================================================

            /**
             * check the callback data with the preload confirm data saved in the database
             */
            $order_info = VirtueMartModelOrders::getOrder($virtuemart_order_id);
            if (($paymentTable->order_number!=$order_info['details']['BT']->order_number) OR 
                    (number_format($paymentTable->payment_order_total, 2, '.', '')!=$callback_msg['price'])) {
                    $this->exitf( 'ERR_CALLBACK_DATA' );
                }  
            
            $modelOrder = VmModel::getModel ('orders');
            if ( $callback_msg['status']=='success' ) {
                    $order = array();
                    $order['order_status'] = $method->status_confirmed;
                    $order['customer_notified'] = 1;
                    $order['comments'] = vmText::sprintf ('VMPAYMENT_MANDARINPAY_PAYMENT_STATUS_CONFIRMED', $paymentTable->order_number);
                    $modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, true);
					
                    //We delete the old stuff
                    // get the correct cart session
                    //$cart = VirtueMartCart::getCart();
                    //$cart->emptyCart();
					$this->emptyCart( $paymentTable->user_session, $paymentTable->order_number ); 
                    $this->exitf( 'OK' );            
                }
            else {
                    $order['order_status'] = $method->status_cancelled;
                    $order['customer_notified'] = 0;
                    $order['comments'] = vmText::sprintf ('VMPAYMENT_MANDARINPAY_PAYMENT_STATUS_CANCELLED', $paymentTable->order_number);
                    $modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
                    $this->logInfo ('After status updated to cancelled for order' . $paymentTable->order_number, 'message');
                    $this->exitf( 'ERR_FUNCTION' );
                }
            
            } 

        /**
         * plgVmOnPaymentResponseReceived
         * This event is fired when the  method returns to the shop after the transaction
         *
         * The method itself should send in the URL the parameters needed
         * NOTE for Plugin developers:
         * If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
         *
         * @param int $virtuemart_order_id : should return the virtuemart_order_id
         * @param text $html: the html to display
         * @return mixed Null when this method was not selected, otherwise the true or false
         *
         * @author Valerie Isaksen
         *
         */
          // actions after responce is received, to redirect user to the order result page after confirmation.
        function plgVmOnPaymentResponseReceived(&$html) {
		
			if (!class_exists ('VirtueMartCart')) {
				require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			}
			if (!class_exists ('shopFunctionsF')) {
				require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
			}
			if (!class_exists ('VirtueMartModelOrders')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
			}

			$cart = VirtueMartCart::getCart ();
			$cart->emptyCart ();
            return true;
            } 

        /**
         * What to do after payment cancel
         */
        function plgVmOnUserPaymentCancel() {
                if (!class_exists('VirtueMartModelOrders')) {
                        require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
                }

                $order_number = vRequest::getString('on', '');
                $virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
                if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
                    return NULL;
                }
                if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
                    $this->logInfo ('getOrderIdByOrderNumber payment not found: exit ', 'ERROR');
                    return NULL;
                }
                if (!($paymentTable = $this->getDataByOrderNumber($order_number))) {
                    $this->logInfo ('getDataByOrderId payment not found: exit ', 'ERROR');
                    return NULL;
                }

                VmInfo(vmText::_('VMPAYMENT_MANDARINPAY_PAYMENT_CANCELLED'));
                $session = JFactory::getSession();
                $return_context = $session->getId();
                if (strcmp($paymentTable->user_session, $return_context) === 0) {
                        $this->handlePaymentUserCancel($virtuemart_order_id);
                }
                return TRUE;
            }
            
        public function exitf($msg) {
            ob_start();
            $this->logInfo ('Process callback ' . vmText::sprintf ($msg), 'message');
            ob_end_clean();
            echo $msg;
            jexit();
        }
         
        //==========================================================================================
        //***********      Additional standard vmpayment methods   *****************************
        //==========================================================================================
        //FRONTEND
        /**
         * Display stored order payment data
         *
         */
        function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id) {
                if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
                        return null; // Another method was selected, do nothing
                    }

                $db = JFactory::getDBO();
                $q = 'SELECT * FROM `' . $this->_tablename . '` '
                        . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
                $db->setQuery($q);
                if (!($paymentTable = $db->loadObject())) {
                        vmWarn(500, $q . " " . $db->getErrorMsg());
                        return '';
                    }
                $this->getPaymentCurrency($paymentTable);

                $html = '<table class="adminlist">' . "\n";
                $html .=$this->getHtmlHeaderBE();
                $html .= $this->getHtmlRowBE('ARSENALPAY_PAYMENT_NAME', $paymentTable->payment_name);
                $html .= $this->getHtmlRowBE('ARSENALPAY_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total . ' ' . $paymentTable->payment_currency);
                $html .= '</table>' . "\n";
                return $html;
            }   

        /**
         * Calculations for this payment method and final cost with tax calculation etc.
         */
        function getCosts(VirtueMartCart $cart, $method, $cart_prices) {
                    return $cart_prices['salesPrice'];
                }
        /**
         * Check if the payment conditions are fulfilled for this payment method
         * @author: Valerie Isaksen
         *
         * @param $cart_prices: cart prices
         * @param $payment
         * @return true: if the conditions are fulfilled, false otherwise
         *
         */
        protected function checkConditions($cart, $method, $cart_prices) {

            $address = (($cart->ST == 0) ? $cart->BT : $cart->ST);
            $method->min_amount = (!empty($method->min_amount)?$method->min_amount:0);
            $method->max_amount = (!empty($method->max_amount)?$method->max_amount:0);

            $amount = $cart_prices['salesPrice'];
            $amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
                    OR
             ($method->min_amount <= $amount AND ($method->max_amount == 0) ));
            if (!$amount_cond) {
                return false;
            }
            $countries = array();
            if (!empty($method->countries)) {
                if (!is_array($method->countries)) {
                    $countries[0] = $method->countries;
                } else {
                    $countries = $method->countries;
                }
            }

            /**
             * probably did not gave his BT:ST address
             */
            if (!is_array($address)) {
                $address = array();
                $address['virtuemart_country_id'] = 0;
            }

            if (!isset($address['virtuemart_country_id'])) {
                $address['virtuemart_country_id'] = 0;
            }
            if (count($countries) == 0 || in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
                return true;
            }

            return false;
        }
        //=========================================================================================================================
        /*
         * We must reimplement this triggers for joomla 1.7
         */
        /**
         * This event is fired after the payment method has been selected. It can be used to store
         * additional payment info in the cart.
         *
         * @author Max Milbers
         * @author Valerie isaksen
         *
         * @param VirtueMartCart $cart: the actual cart
         * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
         *
         */
        public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {
            return $this->OnSelectCheck($cart);
        }

        /**
         * plgVmDisplayListFEPayment
         * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
         *
         * @param object $cart Cart object
         * @param integer $selected ID of the method selected
         * @return boolean True on succes, false on failures, null when this plugin was not selected.
         * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
         *
         * @author Valerie Isaksen
         * @author Max Milbers
         */
        public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
            return $this->displayListFE($cart, $selected, $htmlIn);
        }
        //===============================================================================
        //FRONEND
        /*
         * plgVmonSelectedCalculatePricePayment
         * Calculate the price (value, tax_id) of the selected method
         * It is called by the calculator
         * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
         * @author Valerie Isaksen
         * @cart: VirtueMartCart the current cart
         * @cart_prices: array the new cart prices
         * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
         *
         *
         */

        public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
            return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
        }
    //==============================================================
        /**
         * plgVmOnCheckAutomaticSelectedPayment
         * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
         * The plugin must check first if it is the correct type
         * 
         * @author Valerie Isaksen
         * @param VirtueMartCart cart: the cart object
         * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
         *
         */
        function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(),  &$paymentCounter) {
            return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
        }
    //======================================================================
        /**
         * This method is fired when showing the order details in the frontend.
         * It displays the method-specific data.
         *
         * @param integer $order_id The order ID
         * @return mixed Null for methods that aren't active, text (HTML) otherwise
         * @author Max Milbers
         * @author Valerie Isaksen
         */
        public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

            $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
        }
    //============================================================================
        /**
         * This method is fired when showing when priting an Order
         * It displays the payment method-specific data.
         *
         * @param integer $_virtuemart_order_id The order ID
         * @param integer $method_id  method used for this order
         * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
         * @author Valerie Isaksen
         */
        function plgVmonShowOrderPrintPayment($order_number, $method_id) {
            return $this->onShowOrderPrint($order_number, $method_id);
        }       
    }

    // No closing tag

    
    
    

	
