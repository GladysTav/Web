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

/** @var array  $displayData */
if (!empty($displayData))
{
	echo '
<table class="w100p shoprule-info">
	<thead>
	<tr>
		<th colspan="4">Checkout Questions</th>
	</tr>
	</thead>
</table>
<table style="width: 100%;">';

	foreach ($displayData as $item)
	{
		echo '<tr>';

		settype($item, 'object');

		if (isset($item->label) && isset($item->html))
		{
			echo '<td style="width: 180px;padding: 3px;line-height: 1.4; white-space: nowrap;">' . $item->label . '</td>';
			echo '<td style="padding: 3px; line-height: 1.4;">' . $item->html . '</td>';
		}

		echo '</tr>';
	}

	echo '</table>';
}
