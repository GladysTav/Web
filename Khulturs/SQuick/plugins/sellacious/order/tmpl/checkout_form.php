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
defined('_JEXEC') or die('Restricted access');

/** @var array  $displayData */
if (!empty($displayData))
{
	?>
	<table style="font-size: 14px; font-family: helvetica,arial,sans-serif; line-height: 26px; color: #333; margin: 10px 0; font-weight: normal; width: 100%;">
	<tbody><?php
	foreach ($displayData as $item)
	{
		if (is_object($item) && isset($item->label) && isset($item->html))
		{
			?>
			<tr>
				<td style="vertical-align: top; width: 20%;"><?php echo $item->label ?></td>
				<td style="vertical-align: top; width: 5%;">:</td>
				<td style="vertical-align: top; width: 75%;"><?php echo $item->html; ?></td>
			</tr><?php
		}
	}
	?>
	<tr>
	</tr>
	</tbody>
	</table><?php
}
