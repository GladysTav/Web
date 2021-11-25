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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var  JLayoutFile  $this */
/** @var  stdClass     $displayData */
$field      = $displayData;
$helper     = SellaciousHelper::getInstance();
$records    = $field->value;
$useTable   = $field->useTable;
$rowsLimit  = $field->rowsLimit;
$percentage = $field->percentage;
$jsonFile   = ArrayHelper::getValue($records, 'data', null);

JText::script('COM_SELLACIOUS_FLD_GRD_SHIPPINGSLABS_LAST_ROW_DELETE_ERROR');

$prefix = 'COM_SELLACIOUS_SHIPPINGSLABS_FIELD_GRID_HEADING';
?>
<div class="bg-color-white shipping-slabs-wrapper" id="<?php echo $field->id ?>_wrapper">
	<input type="hidden" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>"
		   value="<?php echo ($useTable && !empty($jsonFile)) ? htmlspecialchars(json_encode($field->value), ENT_COMPAT, 'UTF-8') : ''; ?>"/>

	<?php
	if ($jsonFile)
	{
		$records      = $helper->filestorage->extractFromFile($jsonFile);
		$field->value = $records;
	}
	?>

	<?php if ($useTable): ?>
		<?php echo $this->subLayout('toolbar', $field); ?>
	<?php endif; ?>

	<table class="table table-striped table-hover table-noborder table-nopadding shipping-slabs-table">
		<thead>
			<tr role="row" class="cursor-pointer v-top">
				<th class="nowrap text-center" style="width: 80px;">
					<?php echo JText::_($prefix . '_RANGE_FROM') ?>
				</th>
				<th class="nowrap text-center" style="width: 80px;">
					<?php echo JText::_($prefix . '_RANGE_TO') ?>
				</th>
				<?php if ($useTable): ?>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_ORIGIN_COUNTRY') ?>
				</th>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_ORIGIN_STATE') ?>
				</th>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_ORIGIN_ZIP') ?>
				</th>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_COUNTRY') ?>
				</th>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_STATE') ?>
				</th>
				<th class="nowrap text-center" style="min-width: 120px;">
					<?php echo JText::_($prefix . '_ZIP') ?>
				</th>
				<?php endif; ?>
				<th class="nowrap text-center" style="width: 120px;">
					<?php echo JText::_($prefix . '_PRICE') ?>
					<?php if (!$percentage): ?>
					<small>(<?php echo $field->currency ?>)</small>
					<?php endif; ?>
				</th>
				<?php if (!$useTable): ?>
				<?php if ($percentage): ?>
					<th class="nowrap text-center">
					</th>
				<?php endif; ?>
				<?php if ($field->unitToggle): ?>
					<th class="text-center" style="width: 10px;">
						<?php echo JText::_($prefix . '_PER_UNIT') ?>
					</th>
				<?php endif; ?>
				<th style="width: 1px;" class="text-center">
					<?php $showBtn = ($rowsLimit > 0 && count($records) > 0 && count($records) >= $rowsLimit) ? false : true; ?>
					<button type="button" id="<?php echo $field->id ?>_add"
							class="btn btn-success fa fa-plus sfssrow-add <?php echo $showBtn ? '' : 'hidden'; ?>"></button>
				</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
		<?php
		$data    = clone $field;
		$options = array('client' => 2, 'debug' => false);

		$data->currency = $field->currency;

		if ($useTable)
		{
			foreach ($records as $i => $record)
			{
				$data->row_index = $i;

				echo $this->subLayout('table_rowtemplate', $data);

				if ($i >= 10)
				{
					break;
				}
			}
		}
		elseif (count($records))
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
