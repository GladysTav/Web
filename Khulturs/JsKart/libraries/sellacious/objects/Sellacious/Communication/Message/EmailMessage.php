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
use Joomla\Registry\Registry;
use SellaciousTable;
use JText;
use RuntimeException;
use Sellacious\Media\Attachment;
use Sellacious\Template\AbstractNotificationTemplate;
use SellaciousTableMailQueue;
use stdClass;

/**
 * EmailMessage Object
 *
 * @since   2.0.0
 */
class EmailMessage extends AbstractMessage
{
	/**
	 * @var    string
	 *
	 * @since   2.0.0
	 */
	protected $name = 'email';

	/**
	 * @var    array
	 *
	 * @since   2.0.0
	 */
	protected $sender;

	/**
	 * @var    string
	 *
	 * @since   2.0.0
	 */
	protected $subject;

	/**
	 * @var    array
	 *
	 * @since   2.0.0
	 */
	protected $replyTo = array();

	/**
	 * @var    Attachment[]
	 *
	 * @since   2.0.0
	 */
	protected $attachments = array();

	/**
	 * @var    bool
	 *
	 * @since   2.0.0
	 */
	protected $isHtml = true;

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
		parent::__construct($name, $title, $description);

		$config = JFactory::getConfig();

		$this->setSender($config->get('mailfrom'), $config->get('fromname'));
	}

	/**
	 * Method to set sender
	 *
	 * @param   string  $email  The message sender email address
	 * @param   string  $name   The message sender name
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setSender($email, $name = null)
	{
		$this->sender = array($email, $name);

		return $this;
	}

	/**
	 * Method to set message subject
	 *
	 * @param   string   $subject  The message subject
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Method to set attachments
	 *
	 * @param   Attachment[]  $attachments  Array of attachments
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setAttachments($attachments)
	{
		$this->attachments = $attachments;

		return $this;
	}

	/**
	 * Method to set message as html or not
	 *
	 * @param   bool  $isHtml  Whether the message is html or not
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setIsHtml($isHtml)
	{
		$this->isHtml = $isHtml;

		return $this;
	}

	/**
	 * Method to set reply to address
	 *
	 * @param   string  $email  The message sender email address
	 * @param   string  $name   The message sender name
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function addReplyTo($email, $name = null)
	{
		$this->replyTo[] = array($email, $name);

		return $this;
	}

	/**
	 * Method to clear reply to addresses
	 *
	 * @since   2.0.0
	 */
	public function clearReplyTo()
	{
		$this->replyTo = array();
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

		$params = new Registry($message->params);

		$this->subject     = $message->subject;
		$this->body        = $message->body;
		$this->recipients  = json_decode($message->recipients);
		$this->sender      = json_decode($message->sender);
		$this->replyTo     = json_decode($message->replyto);
		$this->attachments = @unserialize($params->get('attachments')) ?: array();
		$this->isHtml      = $message->is_html;
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
		if (count($this->recipients) === 0 || $this->subject == '' || $this->body == '')
		{
			throw new Exception('Invalid message');
		}

		$record->subject    = $this->subject;
		$record->body       = $this->body;
		$record->recipients = $this->recipients;
		$record->sender     = $this->sender;
		$record->replyto    = $this->replyTo;
		$record->params     = array('attachments' => serialize($this->attachments));
		$record->is_html    = $this->isHtml;
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
	public function send($retry_limit = 0, $unlock = false)
	{
		if (count($this->recipients) === 0 || $this->subject == '' || $this->body == '')
		{
			throw new Exception('Invalid message');
		}

		// Exit if lock not acquired, some other thread may have the lock!
		if ($this->lock_token === false)
		{
			return;
		}

		try
		{
			$this->sendMail();

			$this->response  = JText::_('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_SENT');
			$this->state     = SellaciousTableMailQueue::STATE_SENT;
			$this->sent_date = JFactory::getDate()->toSql();

			// Find if any attachments need to be deleted after the email is sent
			foreach ($this->attachments as $att)
			{
				if ($att->delete_on_sent)
				{
					$att->delete();
				}
			}

			$this->updateQueue($unlock);
		}
		catch (Exception $e)
		{
			$this->response  = JText::sprintf('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_FAILED_ERROR', $e->getMessage());
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
		$table   = SellaciousTable::getInstance('EmailTemplate');

		$table->load(array('context' => $context, 'state' => 1, 'message_type' => 'email'));

		if (!$table->get('id') || !$table->get('body'))
		{
			return false;
		}

		$subject     = $table->get('subject');
		$body        = $table->get('body');
		$recipients  = array_filter(array_map('trim', explode(',', $table->get('recipients'))));
		$cc          = array_filter(array_map('trim', explode(',', $table->get('cc'))));
		$bcc         = array_filter(array_map('trim', explode(',', $table->get('bcc'))));
		$replyTo     = array_filter(array_map('trim', explode(',', $table->get('replyto'))));
		$sender      = $table->get('sender');
		$attachments = $instance->getAttachments();

		$this->context = $context;

		$this->setSubject($instance->parse($subject));
		$this->setBody($instance->parse($body));

		// Set attachments only when its allowed
		if ($table->get('send_attachment'))
		{
			$this->setAttachments($attachments);
		}

		if ($sender)
		{
			$this->setSender($sender);
		}

		$this->addRecipients($recipients);

		foreach ($cc as $r)
		{
			$this->addRecipient(array($r, '', 'cc'));
		}

		foreach ($bcc as $r)
		{
			$this->addRecipient(array($r, '', 'bcc'));
		}

		foreach ($replyTo as $r)
		{
			$this->addReplyTo($r, '');
		}

		return true;
	}

	/**
	 * Method to set recipient by user id or guest information object (name, email, phone)
	 *
	 * @param   array   $recipients  Array of recipients
	 * @param   string  $type        Type of recipient
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addRawRecipients(array $recipients, $type = null)
	{
		$users  = array_filter($recipients, 'is_numeric');
		$guests = array_filter($recipients, 'is_object');

		if ($users)
		{
			$query = $this->db->getQuery(true);

			$query->select('u.name, u.email')
				->from($this->db->qn('#__users', 'u'))
				->where('u.id IN (' . implode(',', array_map('intval', $users)) . ')');

			$rows = $this->db->setQuery($query)->loadObjectList();

			foreach ($rows as $row)
			{
				$this->addRecipient(array($row->email, $row->name, $type));
			}
		}

		foreach ($guests as $guest)
		{
			if (isset($guest->email))
			{
				$name = isset($guest->name) ? $guest->name : '';

				$this->addRecipient(array($guest->email, $name));
			}
		}
	}

	/**
	 * Process the mail sending task, this should be the role of an API ideally
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function sendMail()
	{
		$subject = $this->subject ?: JText::_('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_NO_SUBJECT');
		$mailer  = JFactory::getMailer();

		$mailer->setSubject($subject);
		$mailer->setBody($this->body);
		$mailer->setFrom($this->sender[0], $this->sender[1]);
		$mailer->isHtml(true);

		foreach ($this->recipients as list($e, $n, $t))
		{
			switch ($t)
			{
				case 'cc':
					$mailer->addCc($e, $n);
					break;
				case 'bcc':
					$mailer->addBcc($e, $n);
					break;
				default:
					$mailer->addRecipient($e, $n);
			}
		}

		foreach ($this->replyTo as $rTo)
		{
			$mailer->addReplyTo($rTo[0], $rTo[1]);
		}

		foreach ($this->attachments as $att)
		{
			$mailer->addAttachment($att->getPath(true), $att->getName(), $att->getEncoding(), $att->getMime(), $att->getDisposition());
		}

		$sent = $mailer->Send();

		if ($sent !== true)
		{
			throw new Exception($sent);
		}
	}
}
