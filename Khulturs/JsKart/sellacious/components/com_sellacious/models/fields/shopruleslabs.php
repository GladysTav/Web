<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('ShippingSlabs');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.7.0
 */
class JFormFieldShopruleSlabs extends JFormFieldShippingSlabs
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	protected $type = 'ShopruleSlabs';

	/**
	 * The rule type.
	 *
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $ruleType = 'shoprule';
}
