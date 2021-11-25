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

/** @var stdClass $displayData */
$field = $displayData;
?>
<div class="bg-color-white pull-left" style="padding: 1px; border: 1px solid #eee;">
	<table id="<?php echo $field->id; ?>" class="table-stripped">

		<thead class="hide-on-flat-price">
			<tr role="row" class="cursor-pointer" style="background: #eee; line-height: 2.2;">
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING_COSTPRICE') ?>
				</th>
				<th class="nowrap text-center" style="width:130px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING_PROFITMARGIN') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING_LISTPRICE') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING_FINALPRICE') ?>
				</th>
				<th class="nowrap text-center" style="width:100px;">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCTPRICES_FIELD_GRID_HEADING_OVERRIDEPRICE') ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php
			$folder  = 'com_sellacious.formfield.productprice';
			$layout  = $field->readonly ? $folder . '.rowreadonly' : $folder . '.rowtemplate';
			$data    = clone $field;
			$options = array('client' => 2, 'debug' => 0);

			echo JLayoutHelper::render($layout, $data, '', $options);
			?>
			<tr class="sfpp-blankrow hidden">
				<td colspan="5"></td>
			</tr>
		</tbody>
	</table>
</div>