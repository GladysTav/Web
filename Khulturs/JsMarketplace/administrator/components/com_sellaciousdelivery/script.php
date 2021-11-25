<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

/**
 * Create an empty class to meet the situation where sellacious backoffice is not installed yet.
 * In this case however, the backoffice part of the component will not be processed and only the joomla frontend and backend files, and the datanse will be installed.
 */
if (!class_exists('SellaciousInstallerComponent'))
{
	class SellaciousInstallerComponent
	{
	}
}

/**
 * Script file of delivery component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_sellaciousdelivery
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class Com_sellaciousdeliveryInstallerScript extends SellaciousInstallerComponent
{
	/**
	 * Method to install the component.
	 * Used to mark installation records for backoffice extensions and create backoffice menu.
	 *
	 * @param   JInstallerAdapterFile $installer
	 *
	 * @since   1.7.0
	 */
	public function install($installer)
	{
		parent::install($installer);

		$db       = JFactory::getDbo();
		$language = JFactory::getLanguage();
		$code     = $language->getTag();
		$language->load('com_sellaciousdelivery', JPATH_ADMINISTRATOR . '/components/com_sellaciousdelivery', $code, true);

		// Create Sellacious menu
		/** @var  \JTableMenu $menu */
		$menu   = JTable::getInstance('Menu');
		$parent = JTable::getInstance('Menu');
		$parent->load(array(
			'alias'     => 'shop',
			'menutype'  => 'sellacious-menu',
			'client_id' => 2,
		));
		$parentId = $parent->get('id', 1);

		$data                 = array();
		$data['menutype']     = 'sellacious-menu';
		$data['client_id']    = 2;
		$data['level']        = 2;
		$data['title']        = JText::_('COM_SELLACIOUSDELIVERY_ORDERS_MENU_TITLE');
		$data['alias']        = 'delivery-orders';
		$data['link']         = 'index.php?option=com_sellaciousdelivery&view=orders';
		$data['type']         = 'component';
		$data['published']    = 1;
		$data['parent_id']    = $parentId;
		$data['component_id'] = $installer->get('extension_id');
		$data['img']          = 'class:component';
		$data['home']         = 0;
		$data['path']         = 'shop/delivery-orders';
		$data['params']       = '{"menu-anchor_css":"paperclip","menu_show":1}';

		$menu->bind($data);
		$menu->check();
		$menu->store();

		$query = $db->getQuery(true);
		$query->update('#__menu');
		$query->set(array('level = 2', 'parent_id = ' . $parentId));
		$query->where('id = ' . $menu->get('id'));

		$db->setQuery($query);

		$db->execute();
	}
}
