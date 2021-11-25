<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_BASE') or die;

/**
 * Class to create and parse routes for the sellacious application
 *
 * @since   1.0.0
 */
class JRouterSellacious extends JRouter
{
	/**
	 * Use alternate name for com_sellacious in URL only
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $alt_component;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   2.0.0
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		if (JPATH_SELLACIOUS_DIR !== 'sellacious')
		{
			$this->alt_component = strtolower('com_' . JPATH_SELLACIOUS_DIR);
		}
	}

	/**
	 * Function to convert a route to an internal URI.
	 *
	 * @param   JUri &$uri The uri.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function parse(&$uri)
	{
		if ($this->alt_component && $uri->getVar('option') === $this->alt_component)
		{
			JFactory::getApplication()->input->set('option', 'com_sellacious');

			$uri->setVar('option', 'com_sellacious');
		}

		return array();
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string $url The internal URL
	 *
	 * @return  string  The absolute search engine friendly URL
	 *
	 * @since   1.5
	 */
	public function build($url)
	{
		// Create the URI object
		$uri = parent::build($url);

		// Get the path data
		$route = $uri->getPath();

		// Add basepath to the uri
		$uri->setPath(JUri::base(true) . '/' . $route);

		if ($this->alt_component && $uri->getVar('option') === 'com_sellacious')
		{
			$uri->setVar('option', $this->alt_component);
		}

		return $uri;
	}
}
