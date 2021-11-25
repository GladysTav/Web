<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View to edit
 *
 * @property int counter
 */
class SellaciousViewOrder extends SellaciousView
{
	/** @var  JObject */
	protected $state;

	/** @var  Registry */
	protected $item;

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		$filename = $this->app->input->get('fname', 'Invoice_' . $this->item->get('order_number'));
		$options  = array(
			'path' => JPATH_SITE . '/images/com_sellacious/orders/invoices/' . $filename . '.pdf',
		);

		$order = new Registry($this->item);

		$this->helper->order->renderInvoicePdf($order, '', $options);

		return true;
	}
}
