<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View to edit
 *
 * @since   2.0.0
 */
class SellaciousViewTransaction extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $action_prefix = 'transaction';
	
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_item = 'transaction';
	
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_list = 'transactions';
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function display($tpl = null)
	{
		$this->helper->core->checkGuest();
		
		$type         = $this->app->getUserState('com_sellacious.edit.transaction.type');
		$showAddFund  = $this->helper->config->get('show_transaction_add_fund', 1);
		$showWithdraw = $this->helper->config->get('show_transaction_place_withdrawal', 1);
		
		if (($type == 'addfund' && !$showAddFund) || ($type == 'withdraw' && !$showWithdraw))
		{
			JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=transactions'));
		}
		
		return parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_SELLACIOUS_TRANSACTION_ADD_FUND'), 'file');
	}
}
