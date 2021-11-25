<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Communication\Api;

// no direct access.
defined('_JEXEC') or die;

use Exception;
use Sellacious\Communication\Message\AbstractMessage;

/**
 * Base class for communication api handlers
 *
 * @since   2.0.0
 */
abstract class AbstractCommunicationApi
{
	/**
	 * The handler name
	 *
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
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	protected $recipients;

	/**
	 * AbstractCommunicationApi constructor.
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
	}

	/**
	 * Performs the mechanism to send the message
	 *
	 * @param   AbstractMessage  $message  The message object
	 *
	 * @return  mixed   The API response
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	abstract public function send($message);

	/**
	 * Set Property data
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
		$this->$name = $value;
	}

	/**
	 * Get Property data
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
}
