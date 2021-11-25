<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  JLayoutFile  $this */
/** @var  stdClass     $displayData */
$field    = $displayData;
$helper   = SellaciousHelper::getInstance();
$records  = $field->value;

$prefix = 'COM_SELLACIOUS_SHOPRULESLABS_FIELD_GRID_HEADING';
?>
<div class="bg-color-white shoprule-slabs-wrapper" id="<?php echo $field->id ?>_wrapper">
	<input type="hidden" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>"
		   value="<?php echo htmlspecialchars(json_encode($field->value), ENT_COMPAT, 'UTF-8'); ?>"/>

	<table class="table table-striped table-hover table-noborder table-nopadding shoprule-slabs-table" style="width: auto;">
		<thead>
			<tr role="row" class="cursor-pointer v-top">
				<th class="nowrap text-center" style="width: 150px;">
					<?php echo JText::_($prefix . '_RANGE_FROM') ?>
				</th>
				<th class="nowrap text-center" style="width: 150px;">
					<?php echo JText::_($prefix . '_RANGE_TO') ?>
				</th>
				<th class="nowrap text-center" style="width: 150px;">
					<?php echo JText::_($prefix . '_PRICE') ?>
				</th>
				<th class="nowrap text-center">
				</th>
				<th style="width: 1px;" class="text-center">
					<button type="button" id="<?php echo $field->id ?>_add"
							class="btn btn-success fa fa-plus sfssrow-add"></button>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$data    = clone $field;
		$options = array('client' => 2, 'debug' => false);

		$data->currency = $field->currency;

		if (count($records))
		{
			foreach ($records as $i => $record)
			{
				$data->row_index = $i;

				echo $this->subLayout('rowtemplate', $data);
			}
		}
		else
		{
			$data->row_index = 0;

			echo $this->subLayout('rowtemplate', $data);
		}
		?>
		<tr class="sfss-blankrow hidden">
			<td colspan="4"></td>
		</tr>
		</tbody>
	</table>
</div>
