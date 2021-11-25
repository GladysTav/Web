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
use JDatabaseDriver;
use JFactory;
use JTable;
use JText;
use Sellacious\Communication\CommunicationHelper;
use Sellacious\Communication\Exception\InvalidCommunicationApiException;
use Sellacious\Template\AbstractNotificationTemplate;
use SellaciousHelper;
use SellaciousTableMailQueue;
use stdClass;

/**
 * Base Class for Communication Classes
 *
 * @since   2.0.0
 */
abstract class AbstractMessage
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The handler title
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $title;

	/**
	 * The handler description
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $description;

	/**
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $context;

	/**
	 * @var  JDatabaseDriver
	 *
	 * @since  2.0.0
	 */
	protected $db;

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $apiName;

	/**
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $body;

	/**
	 * @var    stdClass[]
	 *
	 * @since  2.0.0
	 */
	protected $recipients = array();

	/**
	 * @var    int
	 *
	 * @since  2.0.0
	 */
	protected $id = null;

	/**
	 * @var    int
	 *
	 * @since  2.0.0
	 */
	protected $state;

	/**
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $response;

	/**
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $sent_date;

	/**
	 * @var    int
	 *
	 * @since  2.0.0
	 */
	protected $retries = 0;

	/**
	 * The lock token if locked, false if lock failed, null if lock not required
	 *
	 * @var    string
	 *
	 * @since  2.0.0
	 */
	protected $lock_token = null;

	/**
	 * AbstractMessage constructor.
	 *
	 * @param   string  $name         The handler name
	 * @param   string  $title        The handler title
	 * @param   string  $description  The handler description
	 *
	 * @since   2.0.0
	 */
	public function __construct($name, $title, $description)
	{
		$this->name        = $name;
		$this->title       = $title;
		$this->description = $description;

		try
		{
			$this->db     = JFactory::getDbo();
			$this->helper = SellaciousHelper::getInstance();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Method to get the name of the current handler
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)Message/i', get_class($this), $r))
			{
				throw new \Exception(JText::_('COM_SELLACIOUS_COMMUNICATION_EXCEPTION_CLASS_GET_NAME'), 500);
			}

			$this->name = strtolower($r[1]);
		}

		return $this->name;
	}

	/**
	 * Method to get the queue table object instance
	 *
	 * @return  JTable
	 *
	 * @since   2.0.0
	 */
	public function getTable()
	{
		return JTable::getInstance('MailQueue', 'SellaciousTable');
	}

	/**
	 * Magic method to get property
	 *
	 * @param   string  $name  The property name
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function get($name)
	{
		return isset($this->$name) ? $this->$name : null;
	}

	/**
	 * Run when writing data to inaccessible members. Used to prevent arbitrary property write
	 *
	 * @param   $name   string  Property name
	 * @param   $value  mixed   New property value
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION
	 *
	 */
	public function set($name, $value)
	{
		// Do not write non-existing property or if a setter or an adder exists
		if (property_exists($this, $name)
			&& !method_exists($this, 'set' . ucfirst($name))
			&& !method_exists($this, 'add' . ucfirst($name)))
		{
			$this->$name = $value;
		}
	}

	/**
	 * Method to get message body
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Method to set message body
	 *
	 * @param   string  $body  The message body
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Method to set multiple recipients (where only value if value is available)
	 *
	 * @param   array  $recipients  Array of recipients as an <var>array(address, name, [type])</var> or a string
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addRecipients(array $recipients)
	{
		foreach ($recipients as $recipient)
		{
			$this->addRecipient($recipient);
		}
	}

	/**
	 * Method to set message recipient
	 *
	 * @param   array|string  $recipient  A recipient address (e.g. email address, mobile no etc.) as string
	 *                                    or an array like <var>array(address, name, [type])</var>
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addRecipient($recipient)
	{
		if (is_string($recipient))
		{
			$this->recipients[] = array($recipient, null, null);
		}
		elseif (is_array($recipient))
		{
			$this->recipients[] = $recipient;
		}
	}

	/**
	 * Method to get all recipients
	 *
	 * @since   2.0.0
	 */
	public function getRecipients()
	{
		return $this->recipients;
	}

	/**
	 * Method to clear recipients
	 *
	 * @since   2.0.0
	 */
	public function clearRecipients()
	{
		$this->recipients = array();
	}

	/**
	 * Method to load message from queue id
	 *
	 * @param   int  $id  The Queue record Id
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function loadFromQueue($id)
	{
		$table = $this->getTable();
		$query = $this->db->getQuery(true);

		$query->select('*')->from($table->getTableName())->where('id = ' . (int) $id)->where('type = '. $this->db->q($this->name));

		$message = $this->db->setQuery($query)->loadObject();

		if ($message)
		{
			$this->bindQueue($message);
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
		$this->id         = $message->id;
		$this->context    = $message->context;
		$this->state      = $message->state;
		$this->retries    = $message->retries;
		$this->lock_token = $message->lock_token ?: false;
	}

	/**
	 * Method to prepare a record to be saved to queue
	 *
	 * @param   stdClass  $record
	 *
	 * @return  void
	 *
	 * @throws  Exception  If not valid for queue/send action
	 *
	 * @since   2.0.0
	 */
	abstract public function prepareQueue($record);

	/**
	 * Save the message to queue to be sent separately
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function queue()
	{
		$record = new stdClass;

		if (!$this->context)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COMMUNICATION_QUEUE_INVALID_CONTEXT'));
		}

		if (count($this->recipients) === 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COMMUNICATION_QUEUE_EMPTY_RECIPIENTS'));
		}

		$record->id      = null;
		$record->type    = $this->name;
		$record->context = $this->context;
		$record->state   = SellaciousTableMailQueue::STATE_QUEUED;
		$record->retries = 0;

		$this->prepareQueue($record);

		$table = $this->getTable();

		$table->bind($record);
		$table->check();
		$table->store();

		$this->id         = $table->get('id');
		$this->lock_token = false;
	}

	/**
	 * Method to get message context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Method to get message context
	 *
	 * @param   string
	 *
	 * @since   2.0.0
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * Method to lock the specific record in queue
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function lock()
	{
		if (!$this->id)
		{
			$this->lock_token = null;

			return;
		}

		$table = $this->getTable();
		$now   = JFactory::getDate()->toUnix();
		$query = $this->db->getQuery(true);
		$token = uniqid();

		// Attempt lock using atomic query to check and update atomically
		$query->update($table->getTableName())
			->set('lock_token = ' . $this->db->q($token))
			->set('lock_time = ' . $this->db->q($now))
			->where('id = ' . (int) $this->id);

		// Lock only if unlocked or lock expired (120 sec)
		$query->where('(lock_token = ' . $this->db->q('') . ' OR lock_time + 120 < ' . $this->db->q($now) . ')');

		$this->db->setQuery($query)->execute();

		// Check whether the lock was acquired
		$locked = $this->db->getAffectedRows();

		$this->lock_token = $locked ? $token : false;
	}

	/**
	 * Method to unlock the queue record previously lock
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function unlock()
	{
		// If not locked, exit silently
		if (!$this->lock_token)
		{
			return;
		}

		$table = $this->getTable();
		$now   = JFactory::getDate()->toUnix();
		$query = $this->db->getQuery(true);

		// Attempt unlock
		$query->update($table->getTableName())
			->set('lock_token = ' . $this->db->q(''))
			->set('lock_time = 0')
			->where('id = ' . (int) $this->id);

		// Unlock only if locked with given token or lock expired (120 sec)
		$query->where('(lock_token = ' . $this->db->q($this->lock_token) . ' OR lock_time + 120 < ' . $this->db->q($now) . ')');

		$this->db->setQuery($query)->execute();

		// Now check whether the lock was acquired
		$unlocked = $this->db->getAffectedRows();

		if ($unlocked)
		{
			$this->lock_token = false;
		}
	}

	/**
	 * Method to send the communication
	 *
	 * @param   int   $retry_limit  Number or retries allowed for the message
	 * @param   bool  $unlock       Whether to unlock after processing
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	abstract public function send($retry_limit = 0, $unlock = false);

	/**
	 * Method to update queue with response, send date, etc.
	 *
	 * @param   bool  $unlock  Whether to unlock as well along with update
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function updateQueue($unlock = false)
	{
		// If not locked, exit silently
		if (!$this->lock_token)
		{
			return;
		}

		$table = $this->getTable();
		$query = $this->db->getQuery(true);

		// Attempt update
		$query->update($table->getTableName())
			->set('response = ' . $this->db->q($this->response))
			->set('sent_date = ' . $this->db->q($this->sent_date))
			->set('retries = ' . (int) $this->retries)
			->set('state = ' . (int) $this->state)
			->where('id = ' . (int) $this->id);

		// Update only if locked with given token
		$query->where('lock_token = ' . $this->db->q($this->lock_token));

		if ($unlock)
		{
			$query->set('lock_token = ' . $this->db->q(''))->set('lock_time = 0');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to set a custom communication API to this handler
	 *
	 * @param   string  $apiName  The name of the API to be set
	 *
	 * @return  void
	 *
	 * @throws  InvalidCommunicationApiException
	 *
	 * @since   2.0.0
	 */
	public function setApi($apiName)
	{
		if (!CommunicationHelper::isApiValid($this->name, $apiName))
		{
			throw new InvalidCommunicationApiException(JText::sprintf('COM_SELLACIOUS_COMMUNICATION_EXCEPTION_INVALID_API', $apiName, $this->name));
		}

		$this->apiName = $apiName;
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
	abstract public function buildMessageFromTemplate(AbstractNotificationTemplate $instance, $recipientType = null);

	/**
	 * Method to set recipient by user id or guest information object (name, email, phone)
	 *
	 * @param   array   $recipients  Array of recipients
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function addRawRecipients(array $recipients);

	/**
	 * Method to requeue a message after a failure, limited by maximum retry count
	 *
	 * @param   int  $limit
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function requeue($limit)
	{
		if ($this->retries < $limit)
		{
			$this->retries += 1;
		}
		else
		{
			$this->state = SellaciousTableMailQueue::STATE_IGNORED;
		}
	}
}
