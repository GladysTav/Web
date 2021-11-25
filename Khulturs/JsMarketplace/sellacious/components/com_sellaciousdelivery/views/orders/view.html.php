<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Delivery component
 *
 * @since  1.7.0
 */
class SellaciousdeliveryViewOrders extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'order';

	/** @var  string */
	protected $view_item = 'order';

	/** @var  string */
	protected $view_list = 'orders';

	/** @var array */
	protected $lists = array();

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		$statuses              = $this->helper->order->getStatuses(null);
		$this->lists['status'] = $this->helper->core->arrayAssoc($statuses, 'id', 'title');

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();
	}
}
