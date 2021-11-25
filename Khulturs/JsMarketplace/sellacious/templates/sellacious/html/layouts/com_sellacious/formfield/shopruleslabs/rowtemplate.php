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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var stdClass $displayData */
$field          = $displayData;
$row_index      = $field->row_index;
$precision      = $field->precision;
$currency       = $field->currency;
$currency_class = $field->currency_class;
$value          = isset($field->value[$row_index]) ? (array) $field->value[$row_index] : array();

$min   = ArrayHelper::getValue($value, 'min', 0, 'float');
$max   = ArrayHelper::getValue($value, 'max', 0, 'float');
$price = ArrayHelper::getValue($value, 'price', 0, 'float');
$unit  = ArrayHelper::getValue($value, 'unit', '', 'string');
?>
<tr role="row" id="<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?>" class="sfssrow">
	<td>
		<input type="text" data-input-name="min" class="form-control required sfssrow-min text-right"
			   data-float="<?php echo $precision ?>" value="<?php echo $min ?>" title=""/>
	</td>
	<td>
		<input type="text" data-input-name="max" class="form-control required sfssrow-max text-right"
			   data-float="<?php echo $precision ?>" value="<?php echo $max ?>" title=""/>
	</td>
	<td class="nowrap text-center">
		<input type="text" data-input-name="price" class="form-control required sfssrow-price text-right"
			    value="<?php echo number_format($price, 2, '.', '') . $unit ?>" title=""/>
	</td>
	<td id="<?php echo $field->id ?>_wrap_<?php echo $row_index ?>" class="nowrap text-center btn-group" data-toggle="buttons">
		<label class="btn btn-default pull-left <?php echo $unit == '%' ? 'active' : '';?>">
			<input type="radio" data-input-name="unit" value="%" <?php echo $unit == '%' ? 'checked' : '';?>/>
			<span class="">%</span>
		</label>
		<label class="btn btn-default pull-left <?php echo $unit == '' ? 'active' : '';?>">
			<input type="radio" data-input-name="unit" value="" <?php echo $unit == '' ? 'checked' : '';?>/>
			<span class="<?php echo $currency_class; ?>"><?php echo $currency?></span>
		</label>
	</td>

	<td style="width: 1px;" class="text-center">
		<?php $only = count($field->value) == 1 && $row_index == 0; ?>
		<button type="button" id="<?php echo $field->id ?>_remove_<?php echo $row_index ?>"
				class="btn btn-sm bg-color-red txt-color-white sfssrow-remove"><i class="fa fa-lg fa-times"></i></button>
	</td>
</tr>
<script>
	jQuery(function($) {
		$(document).ready(function() {
			var wrapper = $('#<?php echo $field->id ?>_wrap_<?php echo $row_index ?>');

			var choices = wrapper.find('input[type="radio"]');

			wrapper.find('input[type="radio"]').change(function() {
				var input = $('#<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?> input[data-input-name="price"]');
				var type = $(this).is('input') ? $(this).val() : $(this).find('input').val();
				var amt = input.val();
				amt = parseFloat(amt.replace(/%/g, ''));
				amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
				input.val(amt + type);

				$('#<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?> input[data-input-name="unit"][value="'+type+'"]').attr('checked', true);
				var type2 = type == "%" ? "" : "%";
				$('#<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?> input[data-input-name="unit"][value="'+type2+'"]').attr('checked', false);
			});

			$('#<?php echo $field->id ?>_sfssrow_<?php echo $row_index ?> input[data-input-name="price"]').change(function() {
				var amt = $(this).val();
				var type = /%$/.test(amt) ? '%' : '';
				amt = parseFloat(amt.replace(/%/g, ''));
				amt = isNaN(amt) ? '0.00' : amt.toFixed(2);
				$(this).val(amt + type);
				choices.filter('[value="'+type+'"]').click();
			}).trigger('change');
		});
	});
</script>
