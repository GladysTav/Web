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

// no access
defined('_JEXEC') or die;

use Exception;
use JFactory;
use JHtml;
use JLog;
use Joomla\Event\AbstractEvent;
use JText;
use SellaciousHelper;
use stdClass;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
class MessageNotificationTemplate extends AbstractNotificationTemplate
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
	 * Message object
	 *
	 * @var   object
	 *
	 * @since  2.0.0
	 */
	protected $message;

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
		$this->helper  = SellaciousHelper::getInstance();
		$this->message = $event->getArgument('message');
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
		if ($type == 'recipient' && $this->message)
		{
			return array($this->message->recipient);
		}
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
			$senderName = $this->message->sender ? JFactory::getUser($this->message->sender)->name : JText::_('PLG_SELLACIOUS_MAILQUEUE_SENDER_GUEST');
			
			$data = array(
				'date'         => JHtml::_('date', $this->message->created, 'F d, Y h:i A T'),
				'sender_name'  => $senderName,
				'subject'      => $this->message->title,
				'body'         => $this->message->body,
			);

			$obj = (object) $data;
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
		return 'message';
	}

	/**
	 * Set default variables and sample
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		parent::loadVariables();

		$this->addVariable(new TemplateVariable('date', JText::_('PLG_SELLACIOUS_MAILQUEUE_MESSAGE_SHORTCODE_DATE'), ''));
		$this->addVariable(new TemplateVariable('sender_name', JText::_('PLG_SELLACIOUS_MAILQUEUE_MESSAGE_SHORTCODE_SENDER_NAME'), ''));
		$this->addVariable(new TemplateVariable('subject', JText::_('PLG_SELLACIOUS_MAILQUEUE_MESSAGE_SHORTCODE_SUBJECT'), ''));
		$this->addVariable(new TemplateVariable('body', JText::_('PLG_SELLACIOUS_MAILQUEUE_MESSAGE_SHORTCODE_BODY'), ''));
	}
}
