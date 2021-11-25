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
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/** @var array  $displayData */
$shippingParams = new Registry($displayData);

if ($values = array_filter((array) $shippingParams->toObject(), 'is_object')): ?>
	<br>
	<table class="w100p shoprule-info">
		<thead>
		<tr>
			<th colspan="4">
				<?php echo JText::_('COM_SELLACIOUS_ORDER_HEADING_SHIPPING_PARAMS_VALUES') ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($values as $record): ?>
			<?php if (isset($record->html)): ?>
				<tr>
					<td style="width: 180px;" class="nowrap"><?php echo $record->label ?></td>
					<td><?php echo $record->html  ?></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
