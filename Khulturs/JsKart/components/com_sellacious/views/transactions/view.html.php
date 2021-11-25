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
class SellaciousViewTransactions extends SellaciousView
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
	 * @since   2.0.0
	 */
	public function display($tpl = null)
	{
		$this->helper->core->checkGuest();
		
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
	
	/**
	 * Method to get E-Wallet Balance of user
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getWalletBalance()
	{
		$user_id  = JFactory::getUser()->get('id');
		$currency = $this->helper->currency->getGlobal('code_3');
		$balances = $this->helper->transaction->getBalance($user_id);
		
		$balances = array_filter($balances, function ($value)
		{
			return $value->amount > 0;
		});
		
		foreach ($balances as &$balance)
		{
			$balance->convert_currency = $currency;
			$balance->convert_amount   = $this->helper->currency->convert($balance->amount, $balance->currency, $currency);
			$balance->convert_display  = $this->helper->currency->display($balance->amount, $balance->currency, $currency);
		}
		
		return array_values($balances);
	}
}
