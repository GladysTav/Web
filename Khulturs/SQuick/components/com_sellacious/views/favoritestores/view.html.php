<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of orders.
 *
 * @since  2.0.0
 */
class SellaciousViewFavoriteStores extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 *
	 * @since  2.0.0
	 */
	protected $items;
	
	/**
	 * @var  JPagination
	 *
	 * @since  2.0.0
	 */
	protected $pagination;
	
	/**
	 * @var  JObject
	 *
	 * @since  2.0.0
	 */
	protected $state;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function display($tpl = null)
	{
		$me = JFactory::getUser();

		if ($me->guest)
		{
			$this->app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}

		// Preserve state info
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::ERROR, 'jerror');
			
			return false;
		}
		
		return parent::display($tpl);
	}
}
