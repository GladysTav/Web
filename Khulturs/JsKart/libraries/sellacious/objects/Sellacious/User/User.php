<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.

namespace Sellacious\User;

defined('_JEXEC') or die;

use JFactory;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\User\UserHelper as JUserHelper;
use Sellacious\Access\Access;

/**
 * User class. Handles all application interaction with a user
 *
 * @since  2.0.0
 */
class User
{
	/**
	 * The flag that the wrapper user object is the shop owner and has all the access
	 * This does not provide any access to the Joomla level actions
	 *
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $isAdmin = null;

	/**
	 * The object internal id
	 *
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	public $id;

	/**
	 * Associative array of category names => category ids
	 *
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	public $groups = array();

	/**
	 * Authorised access groups (recursive/inherited)
	 *
	 * @var    array
	 *
	 * @since  2.0.0
	 */
	protected $_authGroups = null;

	/**
	 * @var    array  User instances container.
	 *
	 * @since  2.0.0
	 */
	protected static $instances = array();

	/**
	 * Constructor activating the default information of the language
	 *
	 * @param   int  $identifier  The primary key of the user to load (optional).
	 *
	 * @since   2.0.0
	 */
	public function __construct($identifier = 0)
	{
		$this->id = $identifier;
	}

	/**
	 * Returns the global User object, only creating it if it doesn't already exist.
	 *
	 * @param   int  $identifier  The primary key of the user to load (optional).
	 *
	 * @return  User  The User object.
	 *
	 * @since   2.0.0
	 */
	public static function getInstance($identifier = null)
	{
		if ($identifier === null)
		{
			$id = JFactory::getUser()->id;
		}
		elseif (is_numeric($identifier))
		{
			// Find the user id
			$id = $identifier;
		}
		else
		{
			$id = JUserHelper::getUserId($identifier);
		}

		// If the $id is zero, just return an empty User.
		// Note: Don't cache this user because it'll have a new ID on save!
		if (!$id)
		{
			return new User;
		}

		// Check if the user ID is already cached.
		if (empty(self::$instances[$id]))
		{
			$user = new User($id);

			self::$instances[$id] = $user;
		}

		return self::$instances[$id];
	}

	/**
	 * Method to check User object authorisation against an access control
	 * object and optionally an access extension object
	 *
	 * @param   string  $action     The name of the action to check for permission.
	 * @param   string  $assetname  The name of the asset on which to perform the action.
	 *
	 * @return  boolean  True if authorised
	 *
	 * @since   2.0.0
	 */
	public function authorise($action, $assetname = null)
	{
		// Make sure we only check for core.admin once during the run.
		if ($this->isAdmin === null)
		{
			$user = $this->getUser();

			$this->isAdmin = $user->authorise('core.admin') || (bool) Access::check($this->id, 'app.admin');
		}

		return $this->isAdmin ? true : (bool) Access::check($this->id, $action, $assetname);
	}

	/**
	 * Gets an array of the authorised user groups
	 *
	 * @return  int[]
	 *
	 * @since   2.0.0
	 */
	public function getAuthorisedGroups()
	{
		if ($this->_authGroups === null)
		{
			$this->_authGroups = array();
		}

		if (empty($this->_authGroups))
		{
			$this->_authGroups = UserHelper::getGroupsByUser($this->id);
		}

		return $this->_authGroups;
	}

	/**
	 * Method to retrieve the wrapper user instance for native feature access
	 * Call forwarding is not implemented to keep this straight-forward and to support seamless accessibility
	 *
	 * @return  JUser
	 *
	 * @since   2.0.0
	 */
	public function getUser()
	{
		return JUser::getInstance($this->id);
	}
}
