<?php
/**
 * @version     __DEPLOY_VERSION_
 * @package     Sellacious Toolbar Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package  Sellacious
 *
 * @since    1.7.0
 */
class ModSellaciousToolbar
{
	/**
	 * Checks if the logged in user is valid
	 *
	 * @return  string
	 * @throws  \Exception
	 * @since   1.7.0
	 */
	public static function isUserValid()
	{
		$user     = JFactory::getUser();
		$helper   = SellaciousHelper::getInstance();
		$isSeller = $helper->seller->is($user->id);
		$isStaff  = $helper->staff->is($user->id);

		// If the logged in user is not a superuser, seller, or a staff.
		if ($user->id && !($isSeller || $isStaff))
		{
			return 'hide';
		}
		// If the logged in user is a superuser, seller, or a staff.
		elseif ($user->id && ($isSeller || $isStaff))
		{
			return 'loggedIn';
		}
		// If no user has logged in yet.
		else
		{
			return  'loggedOut';
		}
	}

	/**
	 * Checks if the current page is product detail page and gets the edit link
	 *
	 * @return  bool
	 * @throws  \Exception
	 * @since   1.7.0
	 */
	public static function getEditProductLink()
	{
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$helper = SellaciousHelper::getInstance();
		$option = $app->input->getString('option', '');
		$view   = $app->input->getString('view', '');
		$code   = $app->input->getString('p', '');

		$productLink = '';

		if ($option == 'com_sellacious' && $view == 'product' && !empty($code))
		{
			$helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

			if ($product_id && $seller_uid == $user->id)
			{
				$productLink = JUri::root() . JPATH_SELLACIOUS_DIR . '/index.php?option=com_sellacious&task=product.edit&id=' . $product_id . ':' . $seller_uid;
			}
		}

		return $productLink;
	}

	/**
	 * Get unread messages
	 *
	 * @return  \stdClass[]
	 * @throws  \Exception
	 * @since   1.7.0
	 */
	public static function getUnreadMessages()
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$helper = SellaciousHelper::getInstance();

		$filters  = array(
			'list.join' => array(
				array('left', $db->qn('#__sellacious_message_recipients', 'mr') . ' ON mr.message_id = a.id')
			),
			'list.where' => array(
				'(a.recipient = ' . $user->id . ' OR (a.recipient = -1 AND mr.recipient = ' . $user->id . '))',
				'a.is_read = 0',
				'a.state = 1',
			),
			'list.group' => array('a.id')
		);
		$messages = $helper->message->loadObjectList($filters);

		return $messages;
	}

	/**
	 * Get new orders (or Today's orders)
	 *
	 * @param   int[]  $statuses  Array of orders statuses to select
	 *
	 * @return  \stdClass[]
	 * @throws  \Exception
	 * @since   1.7.0
	 */
	public static function getNewOrders($statuses = array())
	{
		$user   = JFactory::getUser();
		$helper = SellaciousHelper::getInstance();
		$db     = JFactory::getDbo();

		$filters = array(
			'list.join'  => array(
				array('inner', '#__sellacious_order_items b ON b.order_id = a.id'),
				array('left', '#__sellacious_order_status AS os ON os.order_id = a.id AND os.state = 1 AND os.item_uid = ' . $db->q('')),
				array('left', '#__sellacious_statuses AS ss ON ss.id = os.status')
			),
			'list.where' => array(
				'b.seller_uid = ' . $user->id
			),
			'list.group' => 'a.id',
		);

		if (!empty($statuses))
		{
			$filters['list.where'][] = 'ss.id IN (' . implode(',', $statuses) . ')';
		}

		$orders  = $helper->order->loadObjectList($filters);

		return $orders;
	}
}
