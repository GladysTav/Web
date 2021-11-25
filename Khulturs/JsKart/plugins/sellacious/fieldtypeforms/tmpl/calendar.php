<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\Registry\Registry;

/** @var  object  $field */
$date   = $field->value;
$params = isset($field->params) ? $field->params : null;
$params = new Registry($params);
$format = $params->get('format', 'F d, Y H:i A');
$format = str_replace(array('%', 'M', 'p', 'I'), array('', 'i', 'A', 'H'), $format);

if ($date)
{
	echo JHtml::_('date', $date, $format);
}
else
{
	echo '–';
}
