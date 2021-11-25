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
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

if (is_array($field->value))
{
	$field->value = (object) $field->value;
}

if (is_object($field->value))
{
	$value = new Registry($field->value);

	if ($value->get('m') > 0.01)
	{
		echo $helper->unit->explain($field->value, true);
	}
}
elseif (is_string($field->value) && $field->value)
{
	echo $field->value;
}
