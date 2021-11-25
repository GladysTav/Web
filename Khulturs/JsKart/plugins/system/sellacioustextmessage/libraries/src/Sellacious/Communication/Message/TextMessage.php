<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Communication\Message;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JFactory;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Communication\Exception\InvalidCommunicationApiException;
use Sellacious\Config\ConfigHelper;
use Sellacious\Template\AbstractNotificationTemplate;
use SellaciousTableMailQueue;
use stdClass;

/**
 * TextMessage Object
 *
 * @since   2.0.0
 */
class TextMessage extends AbstractMessage
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $name = 'text';

	/**
	 * TextMessage constructor.
	 *
	 * @param   string  $name         The handler name
	 * @param   string  $title        The handler title
	 * @param   string  $description  The handler description
	 *
	 * @since   2.0.0
	 */
	public function __construct($name, $title, $description)
	{
		parent::__construct($name, $title, $description);

		try
		{
			$config = ConfigHelper::getInstance('plg_system_sellacioustextmessage');

			$this->apiName = $config->get('message_api');
		}
		catch (Exception $e)
		{
			// Ignore default
		}
	}

	/**
	 * Method to bind the record loaded from queue to this object
	 *
	 * @param   stdClass  $message
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function bindQueue($message)
	{
		parent::bindQueue($message);

		$this->body       = $message->body;
		$this->recipients = json_decode($message->recipients);
	}

	/**
	 * Method to prepare a record to be saved to queue
	 *
	 * @param   stdClass  $record
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function prepareQueue($record)
	{
		if (count($this->recipients) === 0 || $this->body == '')
		{
			throw new Exception('Invalid message');
		}

		$subject = preg_match('/^.{1,32}\b/s', $this->body, $match) ? $match[0] : substr($this->body, 0, 32);

		$record->subject    = $subject;
		$record->body       = $this->body;
		$record->recipients = $this->recipients;
	}

	/**
	 * Method to send text message
	 *
	 * @param   int   $retry_limit  Number or retries allowed for the message
	 * @param   bool  $unlock       Whether to unlock after processing
	 *
	 * @return  void
	 *
	 * @throws  InvalidCommunicationApiException|Exception
	 *
	 * @since   2.0.0
	 */
	public function send($retry_limit = 0, $unlock = false)
	{
		// Exit if lock not acquired, some other thread may have the lock!
		if ($this->lock_token === false)
		{
			return;
		}

		if (count($this->recipients) === 0 || $this->body == '')
		{
			throw new Exception('Invalid message');
		}

		try
		{
			$api      = CommunicationHelper::getApi($this->name, $this->apiName);
			$response = $api->send($this);

			$this->response  = is_scalar($response) ? $response : json_encode($response);
			$this->state     = SellaciousTableMailQueue::STATE_SENT;
			$this->sent_date = JFactory::getDate()->toSql();

			$this->updateQueue($unlock);
		}
		catch (Exception $e)
		{
			if (isset($api) && $api->get('response'))
			{
				$this->response = $api->get('response');
				$this->response = is_scalar($this->response) ? $this->response : json_encode($this->response);
			}
			else
			{
				$this->response = JText::sprintf('COM_SELLACIOUS_TEXT_MESSAGE_FAILED_ERROR', $e->getMessage());
			}

			$this->sent_date = JFactory::getDate()->toSql();

			$this->requeue($retry_limit);

			$this->updateQueue($unlock);

			// Rethrow for the caller to know
			throw $e;
		}
	}

	/**
	 * Method to build message from template
	 *
	 * @param   AbstractNotificationTemplate  $instance       The template object
	 * @param   string                        $recipientType  Type of recipient
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function buildMessageFromTemplate(AbstractNotificationTemplate $instance, $recipientType = null)
	{
		$context = $instance->getName();
		$context = $recipientType ? $context . '.' . $recipientType : $context;
		$table   = \SellaciousTable::getInstance('EmailTemplate');

		$table->load(array('context' => $context, 'state' => 1, 'message_type' => 'text'));

		if (!$table->get('id') || !$table->get('body'))
		{
			return false;
		}

		$body       = $table->get('body');
		$recipients = array_filter(array_map('trim', explode(',', $table->get('recipients'))));

		$this->context = $context;

		$this->setBody($instance->parse($body));
		$this->addRecipients($recipients);

		return true;
	}

	/**
	 * Method to set multiple recipients (where only value if value is available)
	 *
	 * @param   array   $recipients  Array of recipients
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addRawRecipients(array $recipients)
	{
		$users  = array_filter($recipients, 'is_numeric');
		$guests = array_filter($recipients, 'is_object');

		if ($users)
		{
			$query = $this->db->getQuery(true);

			$query->select('u.name')
				->from($this->db->qn('#__users', 'u'))
				->where('u.id IN (' . implode(',', array_map('intval', $users)) . ')');

			$query->select('a.mobile')
				->join('INNER', $this->db->qn('#__sellacious_profiles', 'a') . ' ON a.user_id = u.id');

			$rows = $this->db->setQuery($query)->loadObjectList();

			foreach ($rows as $row)
			{
				$this->addRecipient(array($row->mobile, $row->name));
			}
		}

		foreach ($guests as $guest)
		{
			if (isset($guest->phone))
			{
				$name = isset($guest->name) ? $guest->name : '';

				$this->addRecipient(array($guest->phone, $name));
			}
		}
	}
}
