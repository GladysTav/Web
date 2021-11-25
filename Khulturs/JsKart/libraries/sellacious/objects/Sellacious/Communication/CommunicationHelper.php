<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Communication;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use JLog;
use Joomla\Event\AbstractEvent;
use Joomla\Utilities\ArrayHelper;
use JTable;
use JText;
use Sellacious\Communication\Exception\InvalidCommunicationApiException;
use Sellacious\Communication\Exception\InvalidCommunicationHandlerException;
use Sellacious\Communication\Exception\InvalidTemplateException;
use Sellacious\Communication\Api\AbstractCommunicationApi;
use Sellacious\Communication\Message\AbstractMessage;
use Sellacious\Communication\Message\EmailMessage;
use Sellacious\Media\Attachment;
use Sellacious\Template\AbstractNotificationTemplate;
use stdClass;

/**
 * Helper class for Communication utility
 *
 * @since   2.0.0
 */
class CommunicationHelper
{
	/**
	 * List of known communication handlers
	 *
	 * @var   stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected static $handlers = array();

	/**
	 * Loaded communication handler instances
	 *
	 * @var    AbstractMessage[]
	 *
	 * @since   2.0.0
	 */
	protected static $instances = array();

	/**
	 * List of known communication api names
	 *
	 * @var    stdClass[][]
	 *
	 * @since   2.0.0
	 */
	protected static $apis = array();

	/**
	 * Loaded communication api instances
	 *
	 * @var    AbstractCommunicationApi[][]
	 *
	 * @since   2.0.0
	 */
	protected static $apiInstances = array();

	/**
	 * Load internal handlers
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function load()
	{
		static $loaded;

		if (!$loaded)
		{
			$loaded = true;

			static::addHandler('email', EmailMessage::class, JText::_('COM_SELLACIOUS_COMMUNICATION_HANDLER_EMAIL_LABEL'), JText::_('COM_SELLACIOUS_COMMUNICATION_HANDLER_EMAIL_DESC'));
		}
	}

	/**
	 * Add a communication handler
	 *
	 * @param   string  $name         System identifier for the communication handler
	 * @param   string  $className    Class name for the communication handler to be instantiated
	 * @param   string  $label        Text label as handler name for display
	 * @param   string  $description  Description for display
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function addHandler($name, $className, $label, $description = null)
	{
		static::load();

		static::$handlers[$name] = (object) array('name' => $name, 'class' => $className, 'label' => $label, 'description' => $description);
	}

	/**
	 * Register a communication api
	 *
	 * @param   string  $handlerName  Handler name for the communication handler
	 * @param   string  $name         System identifier for the price handler
	 * @param   string  $className    Class name for the communication api handler to be instantiated
	 * @param   string  $label        Text label as handler name for display
	 * @param   string  $description  Description for display
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public static function registerApi($handlerName, $name, $className, $label, $description = null)
	{
		$api = array(
			'handler'     => $handlerName,
			'name'        => $name,
			'class'       => $className,
			'label'       => $label,
			'description' => $description,
		);

		static::$apis[$handlerName][$name] = (object) $api;
	}

	/**
	 * Get a communication handler instance by name, create new instance only if not already exists
	 *
	 * @param   string  $name  System identifier for the communication handler to load
	 *
	 * @return  AbstractMessage
	 *
	 * @throws  InvalidCommunicationHandlerException|InvalidCommunicationApiException
	 *
	 * @since   2.0.0
	 */
	public static function getHandler($name)
	{
		static::load();

		if (!isset(static::$instances[$name]))
		{
			$handler = ArrayHelper::getValue(static::$handlers, $name);

			if (!is_object($handler) || !class_exists($handler->class))
			{
				throw new InvalidCommunicationHandlerException(JText::sprintf('COM_SELLACIOUS_COMMUNICATION_EXCEPTION_INVALID_HANDLER', $name));
			}

			static::$instances[$name] = new $handler->class($handler->name, $handler->label, $handler->description);
		}

		return static::$instances[$name];
	}

	/**
	 * Get a list of all known communication handlers
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public static function getHandlers()
	{
		static::load();

		return static::$handlers;
	}

	/**
	 * Get a communication api instance by name, create new instance only if not already exists
	 *
	 * @param   string  $handlerName  Handler name for the communication handler
	 * @param   string  $name         System identifier for the communication api to load
	 * @param   bool    $fallback     Whether to load fallback API in case the target API is not found
	 *
	 * @return  AbstractCommunicationApi
	 *
	 * @since   2.0.0
	 */
	public static function getApi($handlerName, $name, $fallback = false)
	{
		if (!isset(static::$apiInstances[$handlerName][$name]))
		{
			$api = static::isApiValid($handlerName, $name) ? static::$apis[$handlerName][$name] : null;

			if (!is_object($api) || !class_exists($api->class))
			{
				if (!$fallback)
				{
					throw new InvalidCommunicationApiException(JText::sprintf('COM_SELLACIOUS_COMMUNICATION_EXCEPTION_INVALID_API', $name, $handlerName));
				}

				// If API doesn't exist, try fallback
				$apis = static::getApis($handlerName);
				$api  = reset($apis);

				if (!is_object($api) || !class_exists($api->class))
				{
					throw new InvalidCommunicationApiException(JText::sprintf('COM_SELLACIOUS_COMMUNICATION_EXCEPTION_INVALID_API', $name, $handlerName));
				}
			}

			static::$apiInstances[$handlerName][$name] = new $api->class($api->name, $api->label, $api->description);
		}

		return static::$apiInstances[$handlerName][$name];
	}

	/**
	 * Method to check Communication API name if its valid
	 *
	 * @param   string  $handlerName  Handler name for the communication handler
	 * @param   string  $name         System identifier for the communication api to load
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public static function isApiValid($handlerName, $name)
	{
		return isset(static::$apis[$handlerName][$name]);
	}

	/**
	 * Get a list of all known communication apis
	 *
	 * @param   string  $handlerName  Handler name for the communication handler
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public static function getApis($handlerName)
	{
		return isset(static::$apis[$handlerName]) ? static::$apis[$handlerName] : array();
	}

	/**
	 * Send notifications to relevant users for the given events
	 *
	 * @param   AbstractEvent  $event
	 * @param   string         $templateClass
	 * @param   string[]       $recipientTypes
	 *
	 * @return  void
	 *
	 * @throws  InvalidTemplateException|Exception
	 *
	 * @since   2.0.0
	 */
	public static function eventNotification(AbstractEvent $event, $templateClass, array $recipientTypes)
	{
		if (!class_exists($templateClass))
		{
			throw new InvalidTemplateException(JText::_('COM_SELLACIOUS_COMMUNICATION_TEMPLATE_ERROR_INVALID_TEMPLATE'));
		}

		if (count($recipientTypes) === 0)
		{
			return;
		}

		/** @var  AbstractNotificationTemplate  $instance */
		$instance = new $templateClass($event);
		$handlers = static::getHandlers();

		foreach ($handlers as $handler)
		{
			foreach ($recipientTypes as $recipientType)
			{
				try
				{
					$message = static::getHandler($handler->name);

					// Clear all Recipients (if there is any added statically)
					$message->clearRecipients();

					$built = $message->buildMessageFromTemplate($instance, $recipientType);

					if ($built)
					{
						$recipients = $instance->getRecipients($recipientType);

						if ($recipients)
						{
							$message->addRawRecipients($recipients);
						}

						$message->queue();
					}
				}
				catch (Exception $e)
				{
					JLog::add($e->getMessage(), JLog::WARNING);
				}
			}
		}
	}

	/**
	 * Send the email for the given message object using given email template object to queue
	 * NOTE: This is a temporary method to send emails for now
	 *
	 * @param   JTable    $template      The template table object
	 * @param   string[]  $replacements  The message data to put in the template
	 * @param   string[]  $recipients    List of recipient email addresses
	 * @param   array     $attachments   Email attachments
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public static function addMailToQueue($template, $replacements = array(), $recipients = array(), $attachments = array())
	{
		$recipients = array_filter($recipients);
		$subject    = trim($template->get('subject'));
		$body       = trim($template->get('body'));

		if (is_array($replacements))
		{
			$replacements = array_change_key_case($replacements, CASE_UPPER);

			foreach ($replacements as $code => $replacement)
			{
				$subject = str_replace('%' . $code . '%', $replacement, $subject);
				$body    = str_replace('%' . $code . '%', $replacement, $body);
			}
		}

		try
		{
			/** @var  EmailMessage $mail */
			$mail = static::getHandler('email');

			$mail->setContext($template->get('context'));
			$mail->setSubject($subject);
			$mail->setBody($body);
			$mail->setSender($template->get('sender'));

			$cc      = array_filter(explode(',', $template->get('cc')));
			$bcc     = array_filter(explode(',', $template->get('bcc')));
			$replyTo = array_filter(explode(',', $template->get('replyto')));

			$mail->clearRecipients();

			foreach ($recipients as $r)
			{
				$mail->addRecipient($r);
			}

			foreach ($cc as $r)
			{
				$mail->addRecipient(array($r, '', 'cc'));
			}

			foreach ($bcc as $r)
			{
				$mail->addRecipient(array($r, '', 'bcc'));
			}

			$mail->clearReplyTo();

			foreach ($replyTo as $r)
			{
				$mail->addReplyTo($r);
			}

			if ($template->get('send_attachment') && is_array($attachments))
			{
				foreach ($attachments as $i => $att)
				{
					$attachments[$i] = new Attachment($att['path'], $att['name']);
				}

				$mail->setAttachments($attachments);
			}

			$mail->queue();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING);
		}
	}
}
