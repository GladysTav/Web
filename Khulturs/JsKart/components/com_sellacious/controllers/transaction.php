<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\CMS\Response\JsonResponse;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionHelper;

defined('_JEXEC') or die;

/**
 * Transaction controller class copied from backend for the Wallet related function support in cart.
 *
 * @since   1.2.0
 */
class SellaciousControllerTransaction extends SellaciousControllerForm
{
	/**
	 * @var string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'transactions';

	/**
	 * @var  string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_TRANSACTION';
	
	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array $data An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	protected function allowAdd($data = array())
	{
		$me      = JFactory::getUser();
		$user_id = $me->id;
		
		$gateway   = $this->helper->access->check('transaction.addfund.gateway');
		$gateway_o = $this->helper->access->check('transaction.addfund.gateway.own');
		
		$allow = $gateway || ($user_id == $me->id && $gateway_o);
		
		return $allow;
	}
	
	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data An array of input data.
	 * @param   string $key  The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Strictly not allowed
		return false;
	}
	
	/**
	 * Method to add a new record for addfund.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 *
	 * @since   2.0.0
	 */
	public function add()
	{
		$this->app->setUserState("$this->option.edit.$this->context.type", 'addfund');
		
		if (!parent::add())
		{
			$this->app->setUserState("$this->option.edit.$this->context.type", null);
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Method to add a new record for withdrawal.
	 *
	 * @return  mixed  True if the record can be added, a error object if not.
	 *
	 * @since   2.0.0
	 */
	public function withdraw()
	{
		$this->app->setUserState("$this->option.edit.$this->context.type", 'withdraw');
		
		if (!parent::add())
		{
			$this->app->setUserState("$this->option.edit.$this->context.type", null);
			
			return false;
		}
		
		return true;
	}

	/**
	 * Get wallet balance of the selected user id via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function getWalletBalanceAjax()
	{
		// fixme: Access check
		$user_id = $this->input->post->getInt('user_id');

		try
		{
			if (!$user_id)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			$currency = $this->helper->currency->getGlobal('code_3');
			$balances = $this->helper->transaction->getBalance($user_id);

			$balances = array_filter($balances, function ($value)
			{
				return $value->amount > 0;
			});

			foreach ($balances as &$balance)
			{
				$balance->convert_currency = $currency;
				$balance->convert_amount   = $this->helper->currency->convert($balance->amount, $balance->currency, $currency);
				$balance->convert_display  = $this->helper->currency->display($balance->amount, $balance->currency, $currency);
			}

			$response = array(
				'state'   => 1,
				'message' => '',
				'data'    => array_values($balances),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		jexit();
	}

	/**
	 * Convert wallet balance in a selected currency of the selected seller uid to shop currency; via ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function convertBalanceAjax()
	{
		// fixme: access check
		$userId   = $this->input->post->getInt('user_id');
		$currency = $this->input->post->getString('currency');

		try
		{
			if (!$userId)
			{
				throw new Exception(JText::_($this->text_prefix . '_NO_USER_SPECIFIED'));
			}

			// TODO: Allow conversion to any amount and currency by parameter
			list($balAmt) = TransactionHelper::getUserBalance($userId, $currency);
			$g_currency   = $this->helper->currency->getGlobal('code_3');

			if ($balAmt < 0.01)
			{
				throw new Exception(JText::_($this->text_prefix . '_INVALID_FOREX_PARAMS'));
			}

			$done = TransactionHelper::forexConvert($userId, $balAmt, $currency, $g_currency);

			$response = array(
				'state'   => $done,
				'message' => JText::_($this->text_prefix . ($done ? '_FOREX_SUCCESS' : '_FOREX_FAILED')),
				'data'    => null,
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response);

		jexit();
	}
	
	/**
	 * AJAX Method to save transaction
	 *
	 * @since   2.0.0
	 */
	public function saveAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}
			
			$data = $this->input->get('jform', 'array', 'Array');
			
			$model = $this->getModel('Transaction', 'SellaciousModel');
			$model->save($data);
			
			if ($txn_id = (int) $model->getState('transaction.id'))
			{
				echo new JsonResponse(array('txn_id' => $txn_id));
			}
			else
			{
				throw new Exception(JText::_('COM_SELLACIOUS_TRANSACTION_ERROR_ADDFUND_FAILED'));
			}
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e->getMessage());
		}
		
		jexit();
	}
	
	/**
	 * AJAX Method to perform payment for the transaction
	 *
	 * @since 2.0.0
	 */
	public function setPaymentAjax()
	{
		try
		{
			if (!JSession::checkToken())
			{
				throw new Exception(JText::_('JINVALID_TOKEN'));
			}
			
			$dispatcher = $this->helper->core->loadPlugins();
			
			$txn_id      = $this->input->getInt('txn_id');
			$formData    = $this->input->get('jform', 'array', 'Array');
			$transaction = $this->helper->transaction->getItem($txn_id);
			
			if ($transaction->id == 0 || $transaction->reason != 'addfund.gateway')
			{
				throw new Exception($this->text_prefix . '_PAYMENT_INVALID_ITEM');
			}
			elseif ($transaction->state != 0)
			{
				// We may want to allow cancelled/failed transactions as well
				throw new Exception($this->text_prefix . '_PAYMENT_PROCESSED_ALREADY');
			}
			
			$methodId = ArrayHelper::getValue($formData, 'method_id');
			
			if (empty($methodId))
			{
				throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD'));
			}
			
			$method = $this->helper->paymentMethod->getMethod($methodId);
			
			if (!$method)
			{
				throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD'));
			}
			
			$form = $this->helper->paymentMethod->getForm($methodId);
			
			if (!($form instanceof JForm))
			{
				throw new Exception(JText::_($this->text_prefix . '_PAYMENT_INFO_INVALID_METHOD_FORM'));
			}
			
			if (!$form->validate($formData))
			{
				$messages = array();
				$errors   = $form->getErrors();
				
				foreach ($errors as $error)
				{
					$messages[] = $error instanceof Exception ? $error->getMessage() : $error;
				}
				
				throw new Exception(JText::sprintf($this->text_prefix . '_PAYMENT_INFO_INVALID_FORM_PARAMS', implode('<br>', $messages)));
			}
			
			$params = ArrayHelper::getValue($formData, $method->handler, array(), 'array');
			
			if (empty($params))
			{
				// B/C for release before 1.5.3
				$params = ArrayHelper::getValue($formData, 'params', array(), 'array');
			}
			
			$formData = (object) $formData;
			
			$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.payment', &$formData, true));
			
			// Set payment parameters
			$paymentId = $this->helper->payment->create('transaction', $transaction->id, $transaction->payment_method_id, $transaction->amount, $transaction->currency);
			
			$formData->payment_id = $paymentId;
			
			$dispatcher->trigger('onContentAfterSave', array('com_sellacious.payment', $formData, true));
			
			// These URLs are for quick identification only > actual response cannot be faked coz we check the payment response back here again.
			$token = JSession::getFormToken();
			
			$successLink = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=success&payment_id=' . $paymentId, false);
			$failureLink = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=failure&payment_id=' . $paymentId, false);
			$cancelLink  = JRoute::_('index.php?option=com_sellacious&task=transaction.onPayment&status=cancel&payment_id=' . $paymentId, false);
			
			$this->app->setUserState('com_sellacious.payment.execution.id', $paymentId);
			$this->app->setUserState('com_sellacious.payment.execution.params', $transaction->payment_params);
			$this->app->setUserState('com_sellacious.payment.execution.success', $successLink . '&' . $token . '=1');
			$this->app->setUserState('com_sellacious.payment.execution.failure', $failureLink . '&' . $token . '=1');
			$this->app->setUserState('com_sellacious.payment.execution.cancel', $cancelLink . '&' . $token . '=1');
			
			echo new JsonResponse(array('redirect' => JUri::root() . 'index.php?option=com_sellacious&task=payment.initialize&' . $token . '=1'));
		}
		catch (Exception $e)
		{
			$this->app->setUserState('com_sellacious.payment.execution', null);
			
			echo new JsonResponse($e->getMessage());
		}
		
		jexit();
	}
	
	/**
	 * Post process the transaction after payment
	 * The status will be updated and the user will be notified according to the payment status
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onPayment()
	{
		$payment_id = $this->input->getInt('payment_id');
		
		$payment = $this->helper->payment->getItem($payment_id);
		
		if ($payment->context != 'transaction' || !$payment->order_id)
		{
			$this->setMessage(JText::_($this->text_prefix . '_INVALID_PAYMENT_RESPONSE'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=transactions'));
			
			return false;
		}
		
		if ($payment->state == SellaciousPluginPayment::STATUS_APPROVED)
		{
			$this->helper->transaction->setApproved($payment->order_id);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_APPROVED'), 'success');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_APPROVAL_HOLD)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_APPROVAL_HOLD);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_APPROVAL'), 'info');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_ABORTED)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_CANCELLED);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_ABORTED'), 'notice');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_DECLINED)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_DECLINED);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_FAILED'), 'notice');
		}
		elseif ($payment->state == SellaciousPluginPayment::STATUS_PENDING)
		{
			$this->helper->transaction->setState($payment->order_id, SellaciousHelperTransaction::STATE_PENDING);
			$this->setMessage(JText::_($this->text_prefix . '_PAYMENT_PENDING'), 'notice');
		}
		
		$this->app->setUserState("$this->option.edit.$this->context.data", null);
		$this->app->setUserState("$this->option.edit.$this->context.id", null);
		$this->app->setUserState("$this->option.edit.$this->context.type", null);
		
		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=transactions', false));
		
		return true;
	}
}
