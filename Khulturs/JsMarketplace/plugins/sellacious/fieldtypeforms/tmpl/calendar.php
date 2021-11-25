<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  object  $field */
$date = $field->value;

if ($date)
{
	echo JHtml::_('date', $date, 'F d, Y H:i A');
}
else
{
	echo '–';
}
