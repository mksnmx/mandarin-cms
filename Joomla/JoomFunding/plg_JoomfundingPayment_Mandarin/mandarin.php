<?php
/**
 * @package      Joomfunding
 * @subpackage   Plugins
 * @author       MandarinLtd
 * @copyright    Copyright (C) 2022 <admin@mandarin.io>. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomfunding\Transaction\Transaction;
use Joomfunding\Transaction\TransactionManager;
use Joomla\Utilities\ArrayHelper;
use Jflib\Payment\Result as PaymentResult;

// no direct access
defined('_JEXEC') or die;

jimport('Jflib.init');
jimport('Joomfunding.init');
jimport('Emailtemplates.init');
//jimport('Jflib.libs.Stripe.init');

JObserverMapper::addObserverClassToClass(Joomfunding\Observer\Transaction\TransactionObserver::class, Joomfunding\Transaction\TransactionManager::class, array('typeAlias' => 'com_joomfunding.payment'));

/**
 * Joomfunding Mandarin Payment Plug-in
 *
 * @package      Joomfunding
 * @subpackage   Plug-ins
 */
class plgJoomfundingPaymentMandarin extends Joomfunding\Payment\Plugin
{
    public function __construct(&$subject, $config = array())
    {
        $this->serviceProvider = 'Mandarin';
        $this->serviceAlias    = 'mandarin';

        $this->extraDataKeys   = array(
            'object', 'id', 'created', 'livemode', 'type', 'pending_webhooks', 'request', 'paid',
            'amount', 'currency', 'captured', 'balance_transaction', 'failure_message', 'failure_code',
            'data'
        );

        parent::__construct($subject, $config);
    }

    /**
     * This method prepares a payment gateway - buttons, forms,...
     * That gateway will be displayed on the summary page as a payment option.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param stdClass                 $item    A project data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @throws \InvalidArgumentException
     * @return null|string
     */
    public function onProjectPayment($context, $item, $params)
    {
        //test
        //print_r($item);die();
        $user = JFactory::getUser();
        
        //test
        //print_r($item);
        //print_r($user);
        //die();
        
        //end test
        
        if (strcmp('com_joomfunding.payment', $context) !== 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        // This is a URI path to the plugin folder
        $pluginURI = 'plugins/joomfundingpayment/mandarin';

        // Load the script that initialize the select element with banks.
        JHtml::_('jquery.framework');

        // Get access token
        $apiKeys = $this->getKeys();

        $html   = array();
        $html[] = '<div class="well">';
        $html[] = '<h4><img src="' . $pluginURI . '/images/mandarin_icon.png" height="32" alt="Mandarin" /></h4>';

        if (!$apiKeys['published'] or !$apiKeys['secret']) {
            $html[] = '<p class="bg-warning p-10-5"><span class="fa fa-warning"></span> ' . JText::_($this->textPrefix . '_ERROR_CONFIGURATION') . '</p>';
            $html[] = '</div>'; // Close the div "well".
            return implode("\n", $html);
        }

        // Get image
        $dataImage = (!$this->params->get('logo')) ? '' : 'data-image="' . $this->params->get('logo') . '"';

        // Get company name.
        if (!$this->params->get('company_name')) {
            $dataName = 'data-name="' . htmlentities($this->app->get('sitename'), ENT_QUOTES, 'UTF-8') . '"';
        } else {
            $dataName = 'data-name="' . htmlentities($this->params->get('company_name'), ENT_QUOTES, 'UTF-8') . '"';
        }

        // Get project title.
        $dataDescription = JText::sprintf($this->textPrefix . '_INVESTING_IN_S', htmlentities($item->title, ENT_QUOTES, 'UTF-8'));

        // Get amount.
        $dataAmount = (int)abs($item->amount * 100);

        $dataPanelLabel = (!$this->params->get('panel_label')) ? '' : 'data-panel-label="' . $this->params->get('panel_label') . '"';
        $dataLabel      = (!$this->params->get('label')) ? '' : 'data-label="' . $this->params->get('label') . '"';

        // Prepare optional data.
        $optionalData = array($dataLabel, $dataPanelLabel, $dataName, $dataImage);
        $optionalData = array_filter($optionalData);
        //$transactionId = Jflib\Utilities\StringHelper::generateRandomString(9, 'MNN');

        
        $projectId = $item->id;
        $rewardId  = $item->rewardId;
        //$bankId    = $this->app->input->get('bank_id');
        $amount    = $item->amount;
        $aUserId   = $this->app->getUserState('auser_id');
        $userId    = $user->id;
        $containerHelper  = new Joomfunding\Container\Helper();
        $project          = $containerHelper->fetchProject($this->container, $projectId);
        $paymentSessionContext    = Joomfunding\Constants::PAYMENT_SESSION_CONTEXT . $project->getId();
        $paymentSessionLocal      = $this->app->getUserState($paymentSessionContext);
        $paymentSessionRemote = $this->getPaymentSession(array(
            'session_id'    => $paymentSessionLocal->session_id
        ));
        if (!$paymentSessionRemote->getId()) {
            $recordDate = new JDate();
            $paymentSessionData['user_id']     = $userId;
            $paymentSessionData['auser_id']    = $aUserId; // This is hash user ID used for anonymous users.
            $paymentSessionData['project_id']  = $projectId;
            $paymentSessionData['reward_id']   = $rewardId;
            $paymentSessionData['record_date'] = $recordDate->toSql();

            $paymentSessionRemote->bind($paymentSessionData);
        }
        $paymentSessionRemote->setGateway($this->serviceAlias);
        $paymentSessionRemote->store();
        //test
        //print_r(JFactory::getApplication()->getMenu()->getActive()->id);die();
        
        $html[] = '<form action="https://secure.mandarinpay.com/Pay" method="post">';
        $html[] = $this->generate_form($apiKeys['secret'],array(
		   "callbackUrl" => "https://".$_SERVER['HTTP_HOST']."/index.php?option=com_joomfunding&task=notifier.notify&format=raw&payment_service=mandarin",
		   "customer_email" => $user->email,
		   "merchantId" => $apiKeys['published'],
		   "orderId" => $paymentSessionRemote->getId(),
		   "price" => $item->amount,
		   "returnUrl" => 'https://'.$_SERVER['HTTP_HOST'].'/index.php?option=com_joomfunding&task=notifier.notify&format=raw&payment_service=mandarin&rid='.$item->slug.'&rcatid='.$item->catslug.'&rItemid='.JFactory::getApplication()->getMenu()->getActive()->id,
		));
        $html[] = '<input type="submit" name="submit" value="Заплатить"/>';
        $html[] = '</form>';

        if ($this->params->get('display_info', 0) and $this->params->get('additional_info')) {
            $html[] = '<p>' . htmlentities($this->params->get('additional_info'), ENT_QUOTES, 'UTF-8') . '</p>';
        }

        //if ($this->params->get('test_mode', 1)) {
        //    $html[] = '<p class="bg-info p-10-5 mt-5"><span class="fa fa-info-circle"></span> ' . JText::_($this->textPrefix . '_WORKS_SANDBOX') . '</p>';
        //}

        $html[] = '</div>';

        return implode("\n", $html);
    }


    /**
     * This method processes transaction data that comes from the paymetn gateway.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     *
     * @return null|PaymentResult
     */
    public function onPaymentNotify($context, $params)
    {

        if (isset($_GET['rid'])&&isset($_GET['rcatid'])&&isset($_GET['rItemid'])) {
            $app = JFactory::getApplication();
            $app->enqueueMessage(JText::_($this->textPrefix . '_THANKS'));
            $app->redirect(JRoute::_('index.php?option=com_joomfunding&view=details&id='.$_GET['rid'].'&catid='.$_GET['rcatid'].'&screen=funders&Itemid='.$_GET['rItemid'], false));
            die();
        }
        if (strcmp('com_joomfunding.notify.mandarin', $context) !== 0) {
            return null;
        }
        if ($this->app->isAdmin()) {
            return null;
        }
        
        $doc = JFactory::getDocument();

        // Check document type
        $docType = $doc->getType();
        if (strcmp('raw', $docType) !== 0) {
            return null;
        }

        // Validate request method
        $requestMethod = $this->app->input->getMethod();
        if (strcmp('POST', $requestMethod) !== 0) {
            $this->log->add(
                JText::_($this->textPrefix . '_ERROR_INVALID_REQUEST_METHOD'),
                $this->debugType,
                JText::sprintf($this->textPrefix . '_ERROR_INVALID_TRANSACTION_REQUEST_METHOD', $requestMethod)
            );

            return null;
        } 
        $data=$_POST;

        //Validate sing
        $apiKeys = $this->getKeys();
        if(!$this->check_sign($apiKeys['secret'], $data)) {
            die('Not MandarinPay');
        } else {
            //print_r($input);die();
        }
        
        // Get transaction ID
        $transactionId = $this->app->input->post->get('orderId');
        if (!$transactionId) {
            return null;
        }

        // Get payment session data
        $keys = array(
            'id' => $transactionId
        );
        $paymentSessionRemote = $this->getPaymentSession($keys);

        // Verify the gateway.
        $gateway = $paymentSessionRemote->getGateway();

        // Prepare the array that have to be returned by this method.
        $paymentResult = new PaymentResult;

            $containerHelper  = new Joomfunding\Container\Helper();
            $currency         = $containerHelper->fetchCurrency($this->container, $params);

        // Validate transaction data
        $validData = $this->validateData($data, $currency->getCode(), $paymentSessionRemote);
        //  $validData = $transactionData;
            if ($validData === null) {
                return null;
            }

            // Set the receiver ID.
            $project = $containerHelper->fetchProject($this->container, $validData['project_id']);
            //$project   = Joomfunding\Project::getInstance(JFactory::getDbo(), $validData['project_id']);
            $validData['receiver_id'] = $project->getUserId();

            // Save transaction data.
            // If it is not completed, return empty results.
            // If it is complete, continue with process transaction data
            $transaction = $this->storeTransaction($validData,$project);
            if ($transaction === null) {
                return null;
            }
            // Update the number of distributed reward.
            $rewardId = Joomla\Utilities\ArrayHelper::getValue($validData, "reward_id");
            $reward   = null;
            if (!empty($rewardId)) {
                $reward = $this->updateReward($validData);

                // Validate the reward.
                if (!$reward) {
                    $transactionData["reward_id"] = 0;
                }
            }
            
            // Generate object of data, based on the transaction properties.
            $paymentResult->transaction = $transaction;

            // Generate object of data based on the project properties.
            $paymentResult->project = $project;

            // Generate object of data based on the reward properties.
            if ($reward !== null and ($reward instanceof Joomfunding\Reward)) {
                $paymentResult->reward = $reward;
            }

            // Generate data object, based on the payment session properties.
            $paymentResult->paymentSession = $paymentSessionRemote;

            // Removing intention.
            //$this->removeIntention($paymentSessionRemote, $transaction);
        //}
            //test
            echo 'OK';die();
        return $paymentResult;
    }

    protected function calc_sign($secret, $fields){
		ksort($fields);
		$secret_t = '';
		foreach($fields as $key => $val)
		{
				$secret_t = $secret_t . '-' . $val;
		}
		$secret_t = substr($secret_t, 1) . '-' . $secret;
		return hash("sha256", $secret_t);
	}

    protected function generate_form($secret, $fields){
		$sign = $this->calc_sign($secret, $fields);
		$form = "";
		foreach($fields as $key => $val)
		{
				$form = $form . '<input type="hidden" name="'.$key.'" value="' . htmlspecialchars($val) . '"/>'."\n";
		}
		$form = $form . '<input type="hidden" name="sign" value="'.$sign.'"/>';
		return $form;
	}
    protected function check_sign($secret, $req){
	$sign = $req['sign'];
	unset($req['sign']);
	$to_hash = '';
	if (!is_null($req) && is_array($req)) {
		ksort($req);
		$to_hash = implode('-', $req);
	}

	$to_hash = $to_hash .'-'. $secret;
	$calculated_sign = hash('sha256', $to_hash);
        //test
        //print($calculated_sign ." - ". $sign);die();
        //$filed = "save.txt";
        //$rez = $calculated_sign ." - ". $sign;
        //file_put_contents($filed, $rez);

	return $calculated_sign == $sign;
}
    /**
     * Validate transaction data.
     *
     * @param array                 $data
     * @param string                $currencyCode
     * @param Joomfunding\Payment\Session $paymentSession
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    protected function validateData($data, $currencyCode, $paymentSession)
    {
        $timestamp = ArrayHelper::getValue($data, 'created');
        $date      = new JDate($timestamp);

        // Prepare transaction status.
        $txnStateResult = ArrayHelper::getValue($data, 'status');

        $txnState = 'pending';
        if ($txnStateResult == 'success') {
            $txnState = 'completed';
        }

        $amount = ArrayHelper::getValue($data, 'price');
        //$amount = (float)($amount <= 0) ? 0 : $amount / 100;

        // Prepare transaction data.
        $transactionData = array(
            'investor_id'      => $paymentSession->getUserId(),
            'project_id'       => $paymentSession->getProjectId(),
            'reward_id'        => $paymentSession->getRewardId(),
            'txn_id'           => ArrayHelper::getValue($data, 'orderId'),
            'txn_amount'       => $amount,
            'txn_currency'     => $currencyCode,
            'txn_status'       => $txnState,
            'txn_date'         => $date->toSql(),
            'service_provider' => $this->serviceProvider,
            'service_alias'    => $this->serviceAlias
        );
        //print_r($transactionData);die();

        // Check User Id, Project ID and Transaction ID.
        if (!$transactionData['project_id'] or !$transactionData['txn_id']) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_TRANSACTION_DATA'), $this->errorType, $transactionData);
            return null;
        }
//print_r($transactionData);die();
        // Check if project record exists in database.
        $projectRecord = new Joomfunding\Validator\Project\Record(JFactory::getDbo(), $transactionData['project_id']);
        if (!$projectRecord->isValid()) {
            $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_PROJECT'), $this->errorType, $transactionData);
            return null;
        }
//print_r($transactionData);die();
        // Check if reward record exists in database.
        if ($transactionData['reward_id'] > 0) {
            $rewardRecord = new Joomfunding\Validator\Reward\Record(JFactory::getDbo(), $transactionData['reward_id'], array('state' => Jflib\Constants::PUBLISHED));
            if (!$rewardRecord->isValid()) {
                $this->log->add(JText::_($this->textPrefix . '_ERROR_INVALID_REWARD'), $this->errorType, $transactionData);
                return null;
            }
        }
//print_r($transactionData);die();
        return $transactionData;
    }

    /**
     * Save transaction
     *
     * @param array $transactionData
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Transaction|null
     */
    public function storeTransaction($transactionData, $project)
    {
        // Get transaction by txn ID
        $keys        = array(
            'txn_id' => Joomla\Utilities\ArrayHelper::getValue($transactionData, 'txn_id')
        );
        $transaction = new Joomfunding\Transaction(JFactory::getDbo());
        $transaction->load($keys);

        // DEBUG DATA
        //JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_TRANSACTION_OBJECT'), $this->debugType, $transaction->getProperties()) : null;

        // Check for existed transaction
        // If the current status if completed, stop the process.
        if ($transaction->getId() and $transaction->isCompleted()) {
            return null;
        }

        // Store the transaction data
        $transaction->bind($transactionData, array('extra_data'));
        $transaction->addExtraData($transactionData['extra_data']);
        $transaction->store();
//print_r($transaction);die();
        // DEBUG DATA
        //JDEBUG ? $this->log->add(JText::_($this->textPrefix . '_DEBUG_TRANSACTION_OBJECT_AFTER_STORED_DATA'), $this->debugType, $transaction->getProperties()) : null;

        // If it is not completed (it might be pending or other status),
        // stop the process. Only completed transaction will continue
        // and will process the project, rewards,...
        //if (!$transaction->isCompleted()) {
        //    return null;
        //}

        // Set transaction ID.
        $transactionData['id'] = $transaction->getId();

        // Update project funded amount
        $amount = Joomla\Utilities\ArrayHelper::getValue($transactionData, 'txn_amount');
        $project->addFunds($amount);
        $project->storeFunds();

        return $transactionData;
    }
    
    /**
     * Get the keys from plug-in options.
     *
     * @return array
     */
    protected function getKeys()
    {
        $keys = array();

        //if ($this->params->get('test_mode', 1)) { // Test server published key.
        //    $keys['published'] = trim($this->params->get('test_published_key'));
        //    $keys['secret']    = trim($this->params->get('test_secret_key'));
        //} else {// Live server access token.
            $keys['published'] = trim($this->params->get('published_key'));
            $keys['secret']    = trim($this->params->get('secret_key'));
        //}

        return $keys;
    }
}
