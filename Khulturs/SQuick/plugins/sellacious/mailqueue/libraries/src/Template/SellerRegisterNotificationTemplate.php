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
use Sellacious\User\UserHelper;
use SellaciousHelper;
use stdClass;

class SellerRegisterNotificationTemplate extends AbstractNotificationTemplate
{
	/**
	 * User object
	 *
	 * @var   object
	 *
	 * @since  2.0.0
	 */
	protected $user;

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
		$this->user = $event->getArgument('user');
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

		if ($type == 'user' && $this->user)
		{
			return array($this->user->id);
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
			if ($this->user)
			{
				$helper     = SellaciousHelper::getInstance();
				$filters    = array(
					'list.select' => 'l.title',
					'list.from'   => '#__sellacious_addresses',
					'list.where'  => 'a.user_id = ' . (int)$this->user->id,
					'list.order'  => 'is_primary DESC',
					'list.join'   => array(
						array('inner', '#__sellacious_locations l ON l.id = a.country'),
					)
				);
				$country    = $helper->user->loadResult($filters);
				$mobile     = $helper->profile->loadResult(array('list.select' => 'a.mobile', 'user_id' => $this->user->id));
				$sellerInfo = $helper->seller->loadObject(array(
					'list.select' => 'a.title AS company, a.store_name AS store',
					'user_id'     => $this->user->id,
				));

				$user   = JFactory::getUser($this->user->id);
				$values = array(
					'date'     => JHtml::_('date', 'now', 'F d, Y h:i A T'),
					'name'     => $user->name,
					'username' => $user->username,
					'email'    => $user->email,
					'phone'    => $mobile,
					'company'  => trim($sellerInfo->company) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_COMPANY'),
					'store'    => trim($sellerInfo->store) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_STORE'),
					'country'  => trim($country ?: $helper->location->ipToCountryName(null)) ?: JText::_('PLG_SELLACIOUS_MAILQUEUE_UNKNOWN_COUNTRY'),
				);

				$obj = (object) $values;
			}
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
		return 'seller_register';
	}

	/**
	 * Set default variables and sample
	 *
	 * @since   2.0.0
	 */
	protected function loadVariables()
	{
		parent::loadVariables();

		$this->addVariable(new TemplateVariable('date', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_DATE'), ''));
		$this->addVariable(new TemplateVariable('name', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_NAME'), ''));
		$this->addVariable(new TemplateVariable('username', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_USERNAME'), ''));
		$this->addVariable(new TemplateVariable('email', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_EMAIL'), ''));
		$this->addVariable(new TemplateVariable('phone', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_PHONE'), ''));
		$this->addVariable(new TemplateVariable('company', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_COMPANY'), ''));
		$this->addVariable(new TemplateVariable('store', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_STORE'), ''));
		$this->addVariable(new TemplateVariable('country', JText::_('PLG_SELLACIOUS_MAILQUEUE_SELLER_REGISTER_SHORTCODE_COUNTRY'), ''));
	}
}
