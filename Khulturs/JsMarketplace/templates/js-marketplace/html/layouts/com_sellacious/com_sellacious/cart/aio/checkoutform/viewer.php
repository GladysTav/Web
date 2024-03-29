<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/** @var   array  $displayData */
extract((array) $displayData);

/**
 * @var  Sellacious\Cart  $cart
 * @var  stdClass[]       $values
 */
?>
<table class="table table-bordered mt-3">
	<?php foreach ($values as $record): ?>
	<tr>
		<td style="width: 140px;" class="nowrap"><?php echo $record->label ?></td>
		<td><?php echo $record->html  ?></td>
	</tr>
	<?php endforeach; ?>
</table>
<button type="button" class="btn btn-small pull-right btn-default btn-edit"><i class="fa fa-edit"></i> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHANGE'); ?></button>
