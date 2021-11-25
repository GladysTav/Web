<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View to edit
 */
class SellaciousViewPaymentMethod extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'paymentmethod';

	/** @var  string */
	protected $view_item = 'paymentmethod';

	/** @var  string */
	protected $view_list = 'paymentmethods';
}
