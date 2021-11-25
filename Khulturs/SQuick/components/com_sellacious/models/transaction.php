<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;
use Sellacious\Transaction\TransactionHelper;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 *
 * @since   2.0.0
 */
class SellaciousModelTransaction extends SellaciousModelAdmin
{
	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 *
	 * @since   2.0.0
	 */
	public function validate($form, $data, $group = null)
	{
		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');

		$vData = parent::validate($form, $data, $group);

		if ($type == 'withdraw')
		{
			$withdrawal = $vData['amount'];

			if (!is_array($withdrawal) || !isset($withdrawal['amount'], $withdrawal['currency']))
			{
				$this->setError(JText::sprintf('COM_SELLACIOUS_TRANSACTION_INVALID_AMOUNT'));
			}

			$currency = $this->helper->currency->getFieldValue($withdrawal['currency'], 'code_3');

			try
			{
				list($balAmt) = TransactionHelper::getUserBalance($vData['user_id'], $currency);

				if (round($balAmt - $withdrawal['amount'], 2) < 0.00)
				{
					$bal_d = $this->helper->currency->display($balAmt, $currency, null);
					$amt_d = $this->helper->currency->display($withdrawal['amount'], $currency, null);

					throw new Exception(JText::sprintf('COM_SELLACIOUS_TRANSACTION_INSUFFICIENT_WALLET_BALANCE', $amt_d, $bal_d));
				}

				$vData['amount']   = $withdrawal['amount'];
				$vData['currency'] = $currency;
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}

		return $vData;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$me = JFactory::getUser();

		if (is_object($data))
		{
			$data->user_id = $me->id;
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception  if there is an error loading the form.
	 *
	 * @since   2.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');

		$form->loadFile('transaction_' . $type);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $record  The form data.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function save($record)
	{
		$now = JFactory::getDate()->toSql();

		$currency = $this->helper->currency->getGlobal('code_3');

		list($balance) = TransactionHelper::getUserBalance($record['user_id'], $currency);

		$type = $this->app->getUserState('com_sellacious.edit.transaction.type');

		// approved=1 / disapproved=-1 / pending=0 / locked=2 / cancelled=-2
		$data = array(
			'order_id'   => 0,
			'user_id'    => $record['user_id'],
			'context'    => 'user.id',
			'context_id' => $record['user_id'],
			'amount'     => $record['amount'],
			'currency'   => $currency,
			'txn_date'   => $now,
			'user_notes' => $record['user_notes'],
		);

		if ($type == 'withdraw')
		{
			$data['crdr']    = 'dr';
			$data['reason']  = 'withdraw';
			$data['state']   = '2';
			$data['balance'] = round($balance - $record['amount'], 2);
			$data['notes']   = "Fund withdrawal request for ({$record['amount']} {$currency}) from wallet for user {$record['user_id']}.";
		}
		elseif ($type == 'addfund')
		{
			$data['crdr']    = 'cr';
			$data['reason']  = $record['reason'];
			$data['state']   = '0';
			$data['balance'] = $balance + $record['amount'];
			$data['notes']   = "Add fund ({$record['amount']} {$currency}) into wallet for user {$record['user_id']}.";

			// We need to call a plugin to handle 'gateway' mode transaction. Currently handled by the controller after save.
			$data['payment_method_id'] = isset($record['payment_method_id']) ? $record['payment_method_id'] : null;
			$data['payment_params']    = isset($record['params']) ? $record['params'] : null;
		}

		return parent::save($data);
	}

}
