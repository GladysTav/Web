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
 * View class for a list of messages.
 *
 * @since  2.0.0
 */
class SellaciousViewMessages extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 */
	protected $items;
	
	/** @var  JPagination */
	protected $pagination;
	
	/** @var  JObject */
	protected $state;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		$this->helper->core->checkGuest();
		$this->messagingAllowed();
		
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
	
	protected function messagingAllowed()
	{
		$enabled = $this->helper->config->get('enable_fe_messages', 0);
		
		if (!$enabled)
		{
			JLog::add(JText::_('COM_SELLACIOUS_ERROR_MESSAGING_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			
			$url = JRoute::_('index.php?option=com_sellacious&view=sellacious', false);
			
			$this->app->redirect($url);
		}
	}
}
