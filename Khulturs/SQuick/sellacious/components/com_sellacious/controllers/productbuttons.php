<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Coupons list controller class
 *
 * @since   1.6.0
 */
class SellaciousControllerProductButtons extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 *
	 * @since    1.6.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_PRODUCT_BUTTONS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name
	 * @param   string  $prefix  The model prefix
	 * @param   null    $config  The configuration options for the model instance
	 *
	 * @since   1.6.0
	 *
	 * @return  JModelLegacy
	 */
	public function getModel($name = 'ProductButton', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}