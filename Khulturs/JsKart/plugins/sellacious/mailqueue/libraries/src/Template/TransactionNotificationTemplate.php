<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Template;

use Exception;
use JFactory;
use JHtml;
use JLog;
use Joomla\Event\AbstractEvent;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\User\UserHelper;
use SellaciousHelper;
use stdClass;

// no access
defined('_JEXEC') or die;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
class TransactionNotificationTemplate extends AbstractNotificationTemplate
{
	/**
	 * Sellacious Helper
	 *
	 * @var   SellaciousHelper
	 *
	 * @since  2.0.0
	 */
	protected $helper;

	/**
	 * Transaction object
	 *
	 * @var   object
	 *
	 * @since  2.0.0
	 */
	protected $transaction;

	/**
	 * Transaction mode
	 *
	 * @var   string
	 *
	 * @since  2.0.0
	 */
	protected $mode;

	/**
	 * Constructor
	 *
	 * @param   AbstractEvent  $event
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct(AbstractEvent $event)
	{
		$this->helper      = SellaciousHelper::getInstance();
		$this->transaction = $event->getArgument('transaction');
		$this->mode        = $event->getArgument('mode');
	}

	/**
	 * Get a list of recipients of given type based on the event,
	 * For guest users we can only promise of having email and/or phone
	 *
	 * @param   string  $type  The recipient type
	 *
	 * @return  array  An array containing user id for registered users / an object [name, email, phone] for guests
	 *
	 * @since   2.0.0
	 */
	public function getRecipients($type)
	{
		if ($type == 'admin')
		{
			return UserHelper::getSuperUsers();
		}

		if ($type == 'user')
		{
			return array($this->transaction->context_id);
		}

		return array();
	}

	/**
	 * Load the list data
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	protected function loadObject()
	{
		$obj = null;

		try
		{
			$values = array(
				'txn_number'   => $this->transaction->txn_number,
				'notes'        => $this->transaction->notes,
				'user_notes'   => $this->transaction->user_notes,
				'date'         => JHtml::_('date', $this->transaction->created, 'F d, Y h:i A T'),
				'beneficiary'  => JFactory::getUser($this->transaction->context_id)->name,
				'amount'       => $this->helper->currency->display($this->transaction->amount, $this->transaction->currency, null, true),
				'mode'         => $this->mode,
				'status'       => JText::_('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X_' . (int) $this->transaction->state),
			);

			$obj = ArrayHelper::toObject($values);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}

		return $obj;
	}

	/**
	 * Method to get the name of this template object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * Set default variables and sample
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		parent::loadVariables();

		$this->addVariable(new TemplateVariable('date', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_DATE'), ''));
		$this->addVariable(new TemplateVariable('beneficiary', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_BENEFICIARY'), ''));
		$this->addVariable(new TemplateVariable('txn_number', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_TXN_NUMBER'), ''));
		$this->addVariable(new TemplateVariable('amount', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_AMOUNT'), ''));
		$this->addVariable(new TemplateVariable('mode', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_MODE'), ''));
		$this->addVariable(new TemplateVariable('status', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_STATUS'), ''));
		$this->addVariable(new TemplateVariable('notes', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_NOTES'), ''));
		$this->addVariable(new TemplateVariable('user_notes', JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_SHORTCODE_USER_NOTES'), ''));
	}
}

