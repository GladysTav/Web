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
defined('_JEXEC') or die;

/**
 * Shippingrule form view
 *
 * @since   1.0.0
 */
class SellaciousViewShippingRule extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'shippingrule';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'shippingrule';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'shippingrules';
}
