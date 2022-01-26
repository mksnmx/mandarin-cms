<?php

/*if (!defined('_VALID_MOS') && !defined('_JEXEC')){
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
}*/

/*if (!class_exists ('vmPSPlugin')) {
    require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}*/
if (!class_exists('vmPSPlugin'))
require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');

class plgVmPaymentMandarinbank extends vmPSPlugin
{   
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);  
        
        $this->_loggable   = true;
        $this->tableFields = array_keys($this->getTableSQLFields());
        $this->_tablepkey = 'id'; 
        $this->_tableId = 'id'; 
        $varsToPush = $this->getVarsToPush();
    
        $this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

    }    
    
    protected function getVmPluginCreateTableSQL()
    {
        return $this->createTableSQL('Payment Mandarin Table');
    }
    
    function getTableSQLFields()
    {
        $SQLfields = array(
            'id'                            => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
            'virtuemart_order_id'           => 'int(11) UNSIGNED',
            'order_number'                  => 'char(32)',
            'virtuemart_paymentmethod_id'   => 'mediumint(1) UNSIGNED',
            'payment_name'                  => 'varchar(5000)',
            'payment_order_total'           => 'decimal(15,2) NOT NULL DEFAULT \'0.00\' ',
            'payment_currency'              => 'char(3) '   
        );
        
        return $SQLfields;
    }
    
    function plgVmConfirmedOrder($cart, $order)
    {
        //echo 'Ales';die();
        if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
            return null;
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }
        
        $lang     = JFactory::getLanguage();
        $filename = 'com_virtuemart';
        $lang->load($filename, JPATH_ADMINISTRATOR);
        $vendorId = 0;
        
        $session        = JFactory::getSession();
        $return_context = $session->getId();
        $this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
        
        $html = "";
        
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
        if (!$method->payment_currency)
            $this->getPaymentCurrency($method);

        // получение кода валюты вида "RUB"
        $q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
        $db =& JFactory::getDBO();
        $db->setQuery($q);

        $currency = $db->loadResult();

        $dateexp = date("Y-m-d H:i:s", time() + 24 * 3600);
        $amount = ceil($order['details']['BT']->order_total*100)/100;
        $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order['details']['BT']->order_number);

        $order_email            = $order['details']['BT']->email;
        
        $desc = 'Оплата заказа №'.$order['details']['BT']->order_number;

        $action_url = "https://secure.mandarinpay.com/Pay"; 
        $this->_virtuemart_paymentmethod_id      = $order['details']['BT']->virtuemart_paymentmethod_id;
        $dbValues['payment_name']                = $this->renderPluginName($method);
        $dbValues['order_number']                = $order['details']['BT']->order_number;
        $dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
        $dbValues['payment_currency']            = $currency;
        $dbValues['payment_order_total']         = $amount;
   
        //echo json_encode($dbValues);die();
        $this->storePSPluginInternalData($dbValues);

        $success_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass);
        $fail_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id);
        $call_back_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&pm=mandarinbank&tmpl=component');

        $params = array(
            'merchantId' => $method->merchant_id,
            'price' => $amount,
            'orderId' => $virtuemart_order_id,
            'customer_email' => $order_email,
            'return_url' => $success_url,
            'callback_url' => $call_back_url
        );

        $signature = $this->calc_sign($method->secret, $params);

        $html = '<form action='.$action_url.' method="POST"  name="vm_mandarinbank_form">
                    <input type="hidden" value="'.$method->merchant_id.'" name="merchantId">
                    <input type="hidden" value="'.$amount.'" name="price">                  
                    <input type="hidden" value="'.$virtuemart_order_id.'" name="orderId">
                    <input type="hidden" value="'.$params['customer_email'].'" name="customer_email">
                    <input type="hidden" value="'.$params['callback_url'].'" name="callbackUrl">
                    <input type="hidden" value="'.$params['return_url'].'" name="returnUrl">
                    <input type="hidden" value="'.$signature.'" name="sign">   
                </form>
                <script type="text/javascript">
                    document.forms.vm_mandarinbank_form.submit()
                </script>
                ';
        //test echo $html;die();
        //old $this->wrlog('html');
        return $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $this->renderPluginName($method, $order), 'P');
        //from tco return $this->processConfirmedOrderPaymentResponse(2, $cart, $order, $html, $dbValues['payment_name'], $new_status);
   }
    
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id)
    {
        if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
            return null; // Another method was selected, do nothing
        }
        
        $db = JFactory::getDBO();
        $q  = 'SELECT * FROM `' . $this->_tablename . '` ' . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
        $db->setQuery($q);
        if (!($paymentTable = $db->loadObject())) {
            vmWarn(500, $q . " " . $db->getErrorMsg());
            return '';
        }
        $this->getPaymentCurrency($paymentTable);
        
        $html = '<table class="adminlist">' . "\n";
        $html .= $this->getHtmlHeaderBE();
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_NAME', $paymentTable->payment_name);
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total . ' ' . $paymentTable->payment_currency);
        $html .= '</table>' . "\n";
        return $html;
    }
    
    function getCosts(VirtueMartCart $cart, $method, $cart_prices)
    {
        return 0;
    }
    
    protected function checkConditions($cart, $method, $cart_prices)
    {
        return true;
    }
    
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id)
    {
        return $this->onStoreInstallPluginTable($jplugin_id);
    }
    
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart)
    {
        return $this->OnSelectCheck($cart);
    }
    
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn)
    {
        return $this->displayListFE($cart, $selected, $htmlIn);
    }
    
    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name)
    {
        return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }
    
    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId)
    {
        if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }
        $this->getPaymentCurrency($method);
        
        $paymentCurrencyId = $method->payment_currency;
    }
    
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array())
    {
        return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }
    
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
    {
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }
    
    function plgVmonShowOrderPrintPayment($order_number, $method_id)
    {
        return $this->onShowOrderPrint($order_number, $method_id);
    }    
    
    function plgVmDeclarePluginParamsPaymentVM3( &$data) 
    {
        return $this->declarePluginParams('payment', $data);
    }
    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table)
    {
        return $this->setOnTablePluginParams($name, $id, $table);
    }
    
    
    public function plgVmOnPaymentNotification()
    {   
        $this->wrlog('StartNotification');
        
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

        $orderid = $_POST['orderId'];
        $payment = $this->getDataByOrderId($orderid);
        $method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);        
        $amount = ceil($payment->payment_order_total*100)/100;

        if ($method){
            $this->wrlog('method OK');
            if (count($_POST) && isset($_POST['sign'])) {
               
                if ($_POST['status'] == 'success' && $method->merchant_id == $_POST['merchantId'] ) { 
  
                    $secret_key = $method->secret;         

                    $request = $_POST;
                    $request_sign = $request['sign'];
                    unset($request['sign']);

                    //формируем цифровую подпись
                    ksort($request, SORT_STRING);
                    array_push($request, $secret_key);
                    $str = implode('-', $request);
                    $sign = hash('sha256',$str);
                    $this->wrlog($request_sign. ' = ' .$sign);

                    if ($request_sign == $sign) {
                        $order['order_status'] = $method->status_success;
                        $order['virtuemart_order_id'] = $orderid;
                        $order['customer_notified'] = 1;
                        $order['comments'] = 'Заказ успешно оплачен с помощью Мандарин Банк';
                        $modelOrder = VmModel::getModel('orders');
                        $modelOrder->updateStatusForOneOrder($orderid, $order, true);
                        return header('Status: 200 OK');             
                    } 
                } else {
                        $order['order_status']        = $method->status_canceled;
                        $order['virtuemart_order_id'] = $orderid;
                        $order['customer_notified']   = 0;
                        $order['comments']            = 'Ошибка Оплаты';
                        $modelOrder = VmModel::getModel ('orders');
                        $modelOrder->updateStatusForOneOrder($orderid, $order, true);
                }      
            } else {            
                exit;
                return null;
            }
        } else {
            exit;
            return null;
        }
    } 
    
    function plgVmOnUserPaymentCancel()
    {
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
        
        $order_number = JRequest::getVar('on');
        if (!$order_number)
            return false;
        $db    = JFactory::getDBO();
        $query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";
        
        $db->setQuery($query);
        $virtuemart_order_id = $db->loadResult();
        
        if (!$virtuemart_order_id) {
            return null;
        }
        $this->handlePaymentUserCancel($virtuemart_order_id);
        
        return true;
    }

    function wrlog($content){
/*        $file = $_SERVER['DOCUMENT_ROOT'].'/logs/log.txt';
        $doc = fopen($file, 'a');
   
        file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
        fclose($doc);
       */
    }
    function checkIP(){
        $ip_stack = array(
            'ip_begin'=>'151.80.190.97',
            'ip_end'=>'151.80.190.104'
        );

        if(!ip2long($_SERVER['REMOTE_ADDR'])>=ip2long($ip_stack['ip_begin']) && !ip2long($_SERVER['REMOTE_ADDR'])<=ip2long($ip_stack['ip_end'])){
            $this->wrlog('REQUEST IP'.$_SERVER['REMOTE_ADDR'].'doesnt match');
            die('Ты мошенник! Пшел вон отсюда!');
        }
        return true;
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
            return hash('sha256',$secret_t);
    }
}
