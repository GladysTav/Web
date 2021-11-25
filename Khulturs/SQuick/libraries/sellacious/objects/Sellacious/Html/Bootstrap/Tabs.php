<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Html\Bootstrap;

use JFactory;
use JLayoutHelper;

/**
 * @package  Tabs class for ctech-bootstrap
 *
 * @since    2.0.0
 */

class Tabs
{
	protected static $activeTab = '';

	protected static $animateTab = 'fade';

	protected static $tabType = 'tabs';

	protected static $vertical = false;

	/**
	 * Starts ctech-bootstrap Tabs
	 *
	 * @param   int    $id       id of the tab set
	 * @param   array  $options  options for tab (Active, Animate, type, vertical)
	 *
	 * @return  string  HTML markup for a ctech-bootstrap Tab
	 *
	 * @since   2.0.0
	 */
	public static function startTabs($id, $options)
	{
		self::$activeTab   = isset($options['active']) ? $options['active']  : '';
		self::$animateTab  = isset($options['animate']) ? $options['animate'] == false ? '' : 'ctech-fade' : 'ctech-fade';

		self::$tabType     = $params['type']     = isset($options['type']) ? $options['type'] === 'pills' ? 'pills' : 'tabs' : 'tabs';
		self::$vertical    = $params['vertical'] = isset($options['vertical']) ? $options['vertical'] : false;
		$params['id']      = $id;

		return JLayoutHelper::render('sellacious.ctech-bootstrap.startTabs', $params);
	}

	/**
	 * Adds a ctech-bootstrap Tab
	 *
	 * @param   int     $id  id of the tab
	 * @param   string  $label  Label of the tab
	 * @param   string  $parent  id of parent tab set
	 *
	 * @return  string  HTML markup for a ctech-bootstrap Tab
	 *
	 * @since   2.0.0
	 */
	public static function addTab($id, $label, $parent)
	{
		$params = array();

		if ($id)    $params['id']     = $id;
		if ($label) $params['label']  = $label;
		if ($parent) $params['parent'] = $parent;

		$params['active']  = self::$activeTab === $id ? true : false;
		$params['animate'] = self::$animateTab;
		$params['type']     = self::$tabType;

		JFactory::getDocument()->addScriptDeclaration(JLayoutHelper::render('sellacious.ctech-bootstrap.addTabScript', $params));

		return JLayoutHelper::render('sellacious.ctech-bootstrap.addTab', $params);
	}

	/**
	 * Ends the ctech-bootstrap Tab
	 *
	 * @return  string  HTML markup for ending a ctech-bootstrap Tab
	 *
	 * @since   2.0.0
	 */
	public static function endTab()
	{
		return JLayoutHelper::render('sellacious.ctech-bootstrap.endTab');
	}

	/**
	 * Ends the ctech-bootstrap Tabs
	 *
	 * @return  string  HTML markup for ending the ctech-bootstrap Tabs
	 *
	 * @since   2.0.0
	 */
	public static function endTabs()
	{
		$params['vertical'] = self::$vertical;

		return JLayoutHelper::render('sellacious.ctech-bootstrap.endTabs', $params);
	}
}
