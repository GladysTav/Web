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
defined('_JEXEC') or die('Restricted access');

use Joomla\Event\Event;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Template\MessageNotificationTemplate;
use Sellacious\Template\SellerRegisterNotificationTemplate;
use Sellacious\Template\TransactionNotificationTemplate;
use Sellacious\User\UserHelper;

jimport('sellacious.loader');

/**
 * Class plgSellaciousMailqueue
 *
 * @since   1.4.5
 */
class plgSellaciousMailQueue extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 *
	 * @since   1.4.5
	 */
	protected $autoloadLanguage = true;

	/**
	 * store previous record before modifying it
	 *
	 * @var    array
	 *
	 * @since   1.6.0
	 */
	protected $previousRecord = array();

	/**
	 * Register library for template classes
	 *
	 * @since   2.0.0
	 */
	public function onAfterInitialise()
	{
		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries/src', false, false, 'psr4');
	}

	/**
	 * Adds order email template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.4.5
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() != 'com_sellacious.emailtemplate')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchEmailContext('com_sellacious.emailtemplate', $contexts);

		if (!empty($contexts))
		{
			$array = is_object($data) ? Joomla\Utilities\ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				if (strpos($array['context'], 'client') !== false)
				{
					$form->loadFile(__DIR__ . '/forms/client_authorised.xml', false);
				}
				elseif (strpos($array['context'], 'message') !== false)
				{
					$form->setFieldAttribute('short_codes', 'template_class', MessageNotificationTemplate::class);
				}
				elseif (strpos($array['context'], 'seller') !== false)
				{
					$form->setFieldAttribute('short_codes', 'template_class', SellerRegisterNotificationTemplate::class);
				}
				elseif (strpos($array['context'], 'transaction') !== false)
				{
					$form->setFieldAttribute('short_codes', 'template_class', TransactionNotificationTemplate::class);
				}
				elseif (strpos($array['context'], 'product') !== false)
				{
					$form->loadFile(__DIR__ . '/forms/product.xml', false);
				}
				elseif (strpos($array['context'], 'question') !== false)
				{
					$form->loadFile(__DIR__ . '/forms/question.xml', false);
				}

				$form->removeField('send_attachment');
			}
		}

		return true;
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['client_authorised_add.admin']         = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_ADD_ADMIN');
			$contexts['client_authorised_add.client']        = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_ADD_CLIENT');
			$contexts['client_authorised_add.user']          = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_ADD_USER');
			$contexts['client_authorised_update.admin']      = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_UPDATE_ADMIN');
			$contexts['client_authorised_update.client']     = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_UPDATE_CLIENT');
			$contexts['client_authorised_update.user']       = JText::_('PLG_SELLACIOUS_MAILQUEUE_CLIENT_AUTHORISED_UPDATE_USER');
			$contexts['message.recipient']                   = JText::_('PLG_SELLACIOUS_MAILQUEUE_MESSAGE_RECIPIENT');
			$contexts['question.admin']                      = JText::_('PLG_SELLACIOUS_MAILQUEUE_QUESTION_ADMIN');
			$contexts['question.seller']                     = JText::_('PLG_SELLACIOUS_MAILQUEUE_QUESTION_SELLER');
			$contexts['question.self']                       = JText::_('PLG_SELLACIOUS_MAILQUEUE_QUESTION_USER');
			$contexts['question.reply']                      = JText::_('PLG_SELLACIOUS_MAILQUEUE_QUESTION_REPLY');
			$contexts['seller_register.admin']               = JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_ADMIN');
			$contexts['seller_register.user']                = JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SELLER');
			$contexts['transaction_initiate_addfund.admin']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_INITIATE_ADDFUND_ADMIN');
			$contexts['transaction_initiate_addfund.user']   = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_INITIATE_ADDFUND_USER');
			$contexts['transaction_initiate_withdraw.admin'] = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_INITIATE_WITHDRAW_ADMIN');
			$contexts['transaction_initiate_withdraw.user']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_INITIATE_WITHDRAW_USER');
			$contexts['transaction_approved_addfund.admin']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_APPROVED_ADDFUND_ADMIN');
			$contexts['transaction_approved_addfund.user']   = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_APPROVED_ADDFUND_USER');
			$contexts['transaction_approved_withdraw.admin'] = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_APPROVED_WITHDRAW_ADMIN');
			$contexts['transaction_approved_withdraw.user']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_APPROVED_WITHDRAW_USER');
			$contexts['transaction_declined_addfund.admin']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_DECLINED_ADDFUND_ADMIN');
			$contexts['transaction_declined_addfund.user']   = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_DECLINED_ADDFUND_USER');
			$contexts['transaction_declined_withdraw.admin'] = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_DECLINED_WITHDRAW_ADMIN');
			$contexts['transaction_declined_withdraw.user']  = JText::_('PLG_SELLACIOUS_MAILQUEUE_TRANSACTION_DECLINED_WITHDRAW_USER');
			$contexts['product.admin']                       = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_ADMIN');
			$contexts['product_query.recipient']             = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_QUERY');
		}
	}

	/**
	 * Before save content method.
	 *
	 * @param   string    $context  The calling context
	 * @param   stdClass  $object   Holds the new message data
	 * @param   boolean   $isNew    If the content is just created
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function onContentBeforeSave($context, $object, $isNew)
	{
		if (!class_exists('SellaciousHelper'))
		{
			return true;
		}

		switch ($context)
		{
			case 'com_sellacious.question':
				$prevRecord = $this->getQuestion($object->id);
				$prevRecord = is_object($prevRecord) ? ArrayHelper::fromObject($prevRecord) : (array) $prevRecord;

				if ($prevRecord['state'] == 1 && $prevRecord['answer'] != $object->answer)
				{
					$this->previousRecord = $prevRecord;
				}
				break;
		}

		return true;
	}

	/**
	 * This method sends a registration email on new users created.
	 *
	 * @param   string    $context  The calling context
	 * @param   stdClass  $object   Holds the new message data
	 * @param   boolean   $isNew    If the content is just created
	 *
	 * @return  boolean
	 *
	 * @since   1.4.5
	 */
	public function onContentAfterSave($context, $object, $isNew)
	{
		if (!class_exists('SellaciousHelper'))
		{
			return true;
		}

		switch ($context)
		{
			case 'com_sellacious.message':
				$this->handleMessageSave($object, $isNew);
				break;
			case 'com_sellacious.seller':
				$this->handleSellerSave($object, $isNew);
				break;
			case 'com_sellacious.transaction':
				$this->handleTransaction($object, $isNew);
				break;
			case 'com_sellacious.client.authorised':
				$this->handleClientAuthorisedUser($object, $isNew);
				break;
			case 'com_sellacious.product':
				$this->handleProductSave($object, $isNew);
				break;
			case 'com_sellacious.question':
				$this->handleQuestionSave($object, $isNew);
				break;
			case 'com_sellacious.user':
				$this->handleUserSave($object, $isNew);
				break;
		}

		return true;
	}

	/**
	 * Change the state in core_content if the state in a table is changed
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  bool
	 *
	 * @since   1.4.5
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (!class_exists('SellaciousHelper'))
		{
			return true;
		}

		if ($context == 'com_sellacious.transaction')
		{
			$helper = SellaciousHelper::getInstance();

			foreach ($pks as $pk)
			{
				$object = $helper->transaction->getItem($pk);

				$this->handleTransaction($object, false);
			}
		}

		return true;
	}

	/**
	 * Handle the events for transaction related events
	 *
	 * @param   stdClass  $transaction
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	protected function handleTransaction($transaction, $isNew)
	{
		$states = array(
			SellaciousHelperTransaction::STATE_PENDING       => 'initiate',
			SellaciousHelperTransaction::STATE_APPROVAL_HOLD => 'initiate',
			SellaciousHelperTransaction::STATE_APPROVED      => 'approved',
			SellaciousHelperTransaction::STATE_DECLINED      => 'declined',
			SellaciousHelperTransaction::STATE_CANCELLED     => 'declined',
		);
		$state  = ArrayHelper::getValue($states, $transaction->state);
		$type   = explode('.', $transaction->reason, 2);
		$type   = reset($type);

		if ($state && $transaction->context == 'user.id' && ($type == 'addfund' || $type == 'withdraw'))
		{
			$event = new Event('onHandleTransaction');
			$event->setArgument('transaction', $transaction);

			if ($transaction->reason == 'addfund.gateway')
			{
				$mode = $this->helper->paymentMethod->getFieldValue($transaction->payment_method_id, 'title', JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_PAYMENT_MODE'));
			}
			else
			{
				$mode = JText::_('COM_SELLACIOUS_TRANSACTION_DIRECT_' . strtoupper($transaction->crdr ? $transaction->crdr : 'TX'));
			}

			$event->setArgument('mode', $mode);

			$class = 'Sellacious\Template\Transaction' . ucfirst($state) . ucfirst($type) . 'Template';
			CommunicationHelper::eventNotification($event, $class, array('user', 'admin'));
		}
	}

	/**
	 * Handler for seller registration
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	protected function handleSellerSave($object, $isNew)
	{
		// Seller registration triggers only for frontend registration
		if ($this->app->isSite() && $isNew)
		{
			$event = new Event('onContentAfterSave');
			$event->setArgument('user', $object);

			CommunicationHelper::eventNotification($event, SellerRegisterNotificationTemplate::class, array('admin', 'user'));
		}
	}

	/**
	 * Handler for user registration
	 *
	 * @param   stdClass|JUser  $object
	 * @param   bool            $isNew
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function handleUserSave($object, $isNew)
	{
		// Seller registration triggers only for backoffice registration
		if ($this->app->isClient('sellacious') && $object instanceof JUser && $object->get('seller_category_id') && $isNew)
		{
			$event = new Event('onContentAfterSave');
			$event->setArgument('user', $object);

			CommunicationHelper::eventNotification($event, SellerRegisterNotificationTemplate::class, array('admin', 'user'));
		}
	}

	/**
	 * Handler for seller registration
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @since   1.4.5
	 */
	protected function handleClientAuthorisedUser($object, $isNew)
	{
		$ctx          = $isNew ? 'client_authorised_add' : 'client_authorised_update';
		$replacements = $this->getValues($ctx, $object);

		// Send to beneficiary
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => $ctx . '.user'));

		if ($table->get('state'))
		{
			$recipients = explode(',', $table->get('recipients'));

			if ($table->get('send_actual_recipient'))
			{
				$emails     = (array) $this->getEmailAddresses($object->user_id);
				$recipients = array_merge($emails, $recipients);
			}

			CommunicationHelper::addMailToQueue($table, $replacements, $recipients);
		}

		// Send to concerned client
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => $ctx . '.client'));

		if ($table->get('state'))
		{
			$recipients = explode(',', $table->get('recipients'));

			if ($table->get('send_actual_recipient'))
			{
				$emails     = (array) $this->getEmailAddresses($object->client_uid);
				$recipients = array_merge($emails, $recipients);
			}

			CommunicationHelper::addMailToQueue($table, $replacements, $recipients);
		}

		// Send to administrators
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => $ctx . '.admin'));

		if ($table->get('state'))
		{
			$recipients = explode(',', $table->get('recipients'));

			if ($table->get('send_actual_recipient'))
			{
				$adminEmails = (array) $this->getAdminEmailAddresses();
				$recipients  = array_merge($adminEmails, $recipients);
			}

			CommunicationHelper::addMailToQueue($table, $replacements, $recipients);
		}
	}

	/**
	 * Handler for message save
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   1.4.5
	 */
	protected function handleMessageSave($object, $isNew)
	{
		if ($isNew)
		{
			// Special handling for message of product query type
			if ($object->context === 'product.query')
			{
				$context = 'product_query.recipient';
				$table   = JTable::getInstance('EmailTemplate', 'SellaciousTable');

				$table->load(array('context' => $context));

				// B/C for < v1.6.0, just copy the template
				if (!$table->get('id'))
				{
					$table->load(array('context' => 'message.recipient'));
					$table->set('id', 0);
					$table->set('context', $context);

					$table->store();
				}

				if ($table->get('state'))
				{
					$emails = $this->getEmailAddresses((array) $object->recipient);

					// This can be a bulk email to thousands of users, so split them separately for accessibility
					$oRecipients = explode(',', $table->get('recipients'));

					foreach ($emails as $email)
					{
						if ($table->get('send_actual_recipient'))
						{
							$recipients = array_merge((array) $email, $oRecipients);
						}
						else
						{
							$recipients = $oRecipients;
						}

						CommunicationHelper::addMailToQueue($table, $this->getValues($context, $object), $recipients);
					}
				}
			}
			else
			{
				$event = new Event('onContentAfterSave');
				$event->setArgument('message', $object);

				CommunicationHelper::eventNotification($event, MessageNotificationTemplate::class, array('recipient'));
			}
		}
	}


	/**
	 * Handler for Product save
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function handleProductSave($object, $isNew)
	{
		$helper     = SellaciousHelper::getInstance();
		$isSendMail = $helper->config->get('send_mail_product_creation', 1);

		if ($isNew && $isSendMail)
		{
			$sellerInfo             = $helper->seller->getItem(array('user_id' => $object->created_by));
			$object->seller_company = $sellerInfo ? ($sellerInfo->store_name ?: $sellerInfo->title) : '';
			$object->product_code   = $helper->product->getCode($object->id, 0, $object->created_by);

			// Send to administrators
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'product.admin'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					$recipients = array_merge($this->getAdminEmailAddresses(), $recipients);
				}

				CommunicationHelper::addMailToQueue($table, $this->getValues('product.admin', $object), $recipients);
			}
		}
	}

	/**
	 * Handler for question save
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function handleQuestionSave($object, $isNew)
	{
		$helper  = SellaciousHelper::getInstance();
		$product = $this->getProduct($object->product_id, $object->variant_id, $object->seller_uid);

		if ($product)
		{
			$productName = $product->product_title;

			if ($product->variant_title)
			{
				$productName .= ' -' . $product->variant_title;
			}
		}
		else
		{
			$productName = '';
		}

		$object->product_name = $productName;
		$object->product_code = $product->code;

		$seller = $helper->user->getItem(array('id' => $object->seller_uid));

		if ($isNew)
		{
			$sellerInfo             = $helper->seller->getItem(array('user_id' => $object->seller_uid));
			$object->seller_company = $sellerInfo ? ($sellerInfo->store_name ?: $sellerInfo->title) : '';

			// Send to administrators
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'question.admin'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					$recipients = array_merge($this->getAdminEmailAddresses(), $recipients);
				}

				CommunicationHelper::addMailToQueue($table, $this->getValues('question.admin', $object), $recipients);
			}

			// Send to the customer
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'question.self'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					array_unshift($recipients, $object->questioner_email);
				}

				CommunicationHelper::addMailToQueue($table, $this->getValues('question.self', $object), $recipients);
			}

			// Send to the respective sellers
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'question.seller'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					array_unshift($recipients, $seller->email);
				}

				CommunicationHelper::addMailToQueue($table, $this->getValues('question.seller', $object), $recipients);
			}
		}
		else
		{
			// Check Seller is modifying his reply
			if ($this->previousRecord && $this->previousRecord['id'] == $object->id
				&& $this->previousRecord['answer'] != $object->answer)
			{
				$object->previousAnswer = $this->previousRecord['answer'];
			}
			else
			{
				$object->previousAnswer = '';
			}

			$sellerInfo             = $helper->seller->getItem(array('user_id' => $object->replied_by));
			$object->seller_company = $sellerInfo ? ($sellerInfo->store_name ?: $sellerInfo->title) : '';

			// Send Reply to the customer
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'question.reply'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					array_unshift($recipients, $object->questioner_email);
				}

				CommunicationHelper::addMailToQueue($table, $this->getValues('question.reply', $object), $recipients);
			}
		}
	}

	/**
	 * Get the email addresses for the recipient users
	 *
	 * @param   int[]  $pks
	 *
	 * @return  string[]
	 *
	 * @since   1.4.5
	 */
	protected function getEmailAddresses($pks)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$emails = array();
		$pks    = (array) $pks;

		try
		{
			if (count($pks))
			{
				$query->select('email')->from('#__users')
					->where('id IN (' . implode(',', $pks) . ')');

				$emails = (array) $db->setQuery($query)->loadColumn();
			}
		}
		catch (RuntimeException $e)
		{
		}

		return $emails;
	}

	/**
	 * Get a list of administrator users who can receive administrative emails
	 *
	 * @return  string[]
	 *
	 * @since   1.4.5
	 */
	protected function getAdminEmailAddresses()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			$uids = UserHelper::getSuperUsers();

			$query->select('DISTINCT  u.email')
				->from($db->q('#__users', 'u'))
				->where('u.block = 0')
				->where('u.id IN (' . implode(', ', $uids) . ')');

			$db->setQuery($query);
			$admins = $db->loadColumn();
		}
		catch (Exception $e)
		{
			$admins = array();
		}

		return $admins;
	}

	/**
	 * Get an array of replacement data for an email
	 *
	 * @param   string  $context
	 * @param   object  $object
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	protected function getValues($context, $object)
	{
		$helper      = SellaciousHelper::getInstance();
		$emailParams = $helper->config->getEmailParams();

		switch ($context)
		{
			case 'product_query.recipient':
				$senderName = $object->sender ? JFactory::getUser($object->sender)->name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$values     = array(
					'sitename'     => JFactory::getConfig()->get('sitename'),
					'site_url'     => rtrim(JUri::root(), '/'),
					'email_header' => $emailParams->get('header', ''),
					'email_footer' => $emailParams->get('footer', ''),
					'date'         => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'sender_name'  => $senderName,
					'subject'      => $object->title,
					'body'         => $object->body,
				);
				break;

			case 'seller_register':
				$filters    = array(
					'list.select' => 'l.title',
					'list.from'   => '#__sellacious_addresses',
					'list.where'  => 'a.user_id = ' . (int) $object->id,
					'list.order'  => 'is_primary DESC',
					'list.join'   => array(
						array('inner', '#__sellacious_locations l ON l.id = a.country'),
					)
				);
				$country    = $helper->user->loadResult($filters);
				$mobile     = $helper->profile->loadResult(array('list.select' => 'a.mobile', 'user_id' => $object->id));
				$sellerInfo = $helper->seller->loadObject(array(
					'list.select' => 'a.title AS company, a.store_name AS store',
					'user_id'     => $object->id,
				));

				$user   = JFactory::getUser($object->id);
				$values = array(
					'sitename'     => JFactory::getConfig()->get('sitename'),
					'site_url'     => rtrim(JUri::root(), '/'),
					'email_header' => $emailParams->get('header', ''),
					'email_footer' => $emailParams->get('footer', ''),
					'date'         => JHtml::_('date', 'now', 'F d, Y h:i A T'),
					'name'         => $user->name,
					'username'     => $user->username,
					'email'        => $user->email,
					'phone'        => $mobile,
					'company'      => trim($sellerInfo->company) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_COMPANY'),
					'store'        => trim($sellerInfo->store) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_STORE'),
					'country'      => trim($country ?: $helper->location->ipToCountryName(null)) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_COUNTRY'),
				);
				break;

			case 'client_authorised_add':

			case 'client_authorised_update':
				$profileC   = $helper->profile->getItem(array('user_id' => $object->client_uid));
				$clientC    = $helper->client->getItem(array('user_id' => $object->client_uid));
				$userC      = $helper->user->getItem(array('id' => $object->client_uid));
				$userU      = $helper->user->getItem(array('id' => $object->user_id));
				$c_currency = $helper->currency->forUser($object->client_uid, 'code_3');

				$values = array(
					'sitename'         => JFactory::getConfig()->get('sitename'),
					'site_url'         => rtrim(JUri::root(), '/'),
					'email_header'     => $emailParams->get('header', ''),
					'email_footer'     => $emailParams->get('footer', ''),
					'date'             => JHtml::_('date', 'now', 'F d, Y h:i A T'),
					'client_name'      => $userC->name,
					'client_email'     => $userC->email,
					'client_phone'     => $profileC->mobile,
					'client_company'   => $clientC->business_name ?: JText::_('JNONE'),
					'user_name'        => $userU->name,
					'user_email'       => $userU->email,
					'credit_limit'     => $helper->currency->display($object->credit_limit, $c_currency, $c_currency, false, 2),
					'credit_limit_old' => $helper->currency->display($object->credit_limit_old, $c_currency, $c_currency, false, 2),
				);
				break;

			case 'product.admin':

				if ($object->state == 1)
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_STATUS_PUBLISHED');
				}
				elseif ($object->state == -1)
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_STATUS_APPROVAL_PENDING');
				}
				else
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_STATUS_UNPUBLISHED');
				}

				$values = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->title,
					'status'         => $status,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'seller_company' => $object->seller_company,
				);
				break;

			case 'question.admin':
				$senderName  = $object->questioner_name ? $object->questioner_name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$senderEmail = $object->questioner_email ? $object->questioner_email : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$values      = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->product_name,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'sender_name'    => $senderName,
					'sender_email'   => $senderEmail,
					'question'       => $object->question,
					'answer'         => $object->answer,
					'seller_company' => $object->seller_company,
				);
				break;

			case 'question.seller':
				$senderName  = $object->questioner_name ? $object->questioner_name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$senderEmail = $object->questioner_email ? $object->questioner_email : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$values      = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->product_name,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'sender_name'    => $senderName,
					'sender_email'   => $senderEmail,
					'question'       => $object->question,
					'answer'         => $object->answer,
					'seller_company' => $object->seller_company,
				);
				break;

			case 'question.self':
				$senderName  = $object->questioner_name ? $object->questioner_name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$senderEmail = $object->questioner_email ? $object->questioner_email : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$values      = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->product_name,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'sender_name'    => $senderName,
					'sender_email'   => $senderEmail,
					'question'       => $object->question,
					'answer'         => $object->answer,
					'seller_company' => $object->seller_company,
				);
				break;

			case 'question.reply':
				$senderName  = $object->questioner_name ? $object->questioner_name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$senderEmail = $object->questioner_email ? $object->questioner_email : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
				$values      = array(
					'sitename'        => JFactory::getConfig()->get('sitename'),
					'site_url'        => rtrim(JUri::root(), '/'),
					'date'            => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'    => $object->product_name,
					'product_url'     => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'sender_name'     => $senderName,
					'sender_email'    => $senderEmail,
					'question'        => $object->question,
					'answer'          => $object->answer,
					'previous_answer' => $object->previousAnswer ? $object->previousAnswer : '',
					'seller_company'  => $object->seller_company,
				);
				break;

			default:
				$values = array();
		}

		return $values;
	}

	/**
	 * Get the question record
	 *
	 * @param   int   $id   The Question Id
	 *
	 * @return  stdClass|bool
	 *
	 * @since   1.6.0
	 */
	protected function getQuestion($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('q.*')
			->from('#__sellacious_product_questions AS q')
			->where('q.id = ' . (int) $id);

		try
		{
			$question = $db->setQuery($query)->loadObject();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return false;
		}

		return $question;
	}

	/**
	 * Get Product record from cache
	 *
	 * @param   int   $product_id   Product Id
	 * @param   int   $variant_id   Variant Id
	 * @param   int   $seller_uid   Seller Id
	 *
	 * @return  stdClass
	 *
	 * @since   1.6.0
	 */
	protected function getProduct($product_id, $variant_id, $seller_uid)
	{
		try
		{
			$loader = new ProductsCacheReader;

			$loader->filterValue('product_id', $product_id);
			$loader->filterValue('variant_id', $variant_id);
			$loader->filterValue('seller_uid', $seller_uid);

			$item = $loader->getItem();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return null;
		}

		return $item;
	}
}
