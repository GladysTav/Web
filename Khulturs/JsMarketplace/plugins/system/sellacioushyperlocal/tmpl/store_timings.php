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

/** @var stdClass $displayData */
$days        = $displayData['days'];
$field       = $displayData['field'];
$timeOptions = $displayData['time_options'];
$value       = $field['value'];

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
?>
<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('select.from_time, select.to_time').select2();

		$('.select_24_hrs').on('change', function () {
			if (this.checked) {
				$(this).closest('.sfssrow').find('.from_time').val('12:00 AM').attr('disabled', true).trigger('change.select2');
				$(this).closest('.sfssrow').find('.to_time').val('12:00 AM').attr('disabled', true).trigger('change.select2');
			} else {
				$(this).closest('.sfssrow').find('.from_time').attr('disabled', false);
				$(this).closest('.sfssrow').find('.to_time').attr('disabled', false);
			}
		});

		$('.<?php echo $field['fldName'] ?>-copy-all').on('click', function () {
			var from_time = $(this).closest('.timingsrow').find('select.from_time').val();
			var to_time = $(this).closest('.timingsrow').find('select.to_time').val();

			$('.timings-table-<?php echo $field['fldName'] ?>').find('.from_time').each(function () {
				if (!$(this).closest('.timingsrow').find('.select_24_hrs').is(':checked')) {
					$(this).val(from_time).trigger('change.select2');
				}
			});

			$('.timings-table-<?php echo $field['fldName'] ?>').find('.to_time').each(function () {
				if (!$(this).closest('.timingsrow').find('.select_24_hrs').is(':checked')) {
					$(this).val(to_time).trigger('change.select2');
				}
			});
		});
	});
</script>
<div class="table-responsive timings-wrapper">
	<table class="table table-striped table-hover table-noborder timings-table-<?php echo $field['fldName'] ?>">
		<thead>
		<tr role="row" class="cursor-pointer v-top">
			<th class="nowrap" style="width:60px;">
				<label class="checkbox">
					<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip style-3" title="" onclick="Joomla.checkAll(this, '<?php echo $field['fldName'] ?>');">
					<span><strong><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_CHECK_ALL'); ?></strong></span>
				</label>
			</th>
			<th class="nowrap text-center"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_DURATION'); ?></th>
			<th></th>
			<th style="width: 60px;">
				<label class="checkbox">
					<input type="checkbox" name="checkall-toggle" value="" class="hasTooltip style-3" title="" onclick="Joomla.checkAll(this, '<?php echo $field['id'] ?>');">
					<span><strong><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_CHECK_ALL'); ?></strong></span>
				</label>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php $count = 0;

		foreach ($days as $num => $day)
		{
			$checked = isset($value[$num]['state']) ? ($value[$num]['state'] == 0 ? '' : 'checked="checked"') : '';
			$fullDay = isset($value[$num]['full_day']) ? ($value[$num]['full_day'] == 0 ? '' : 'checked="checked"') : '';
			$from    = isset($value[$num]['from_time']) ? JFactory::getDate($value[$num]['from_time'])->format('h:i A') : '12:00 AM';
			$to      = isset($value[$num]['to_time']) ? JFactory::getDate($value[$num]['to_time'])->format('h:i A') : '11:59 PM';
			?>
			<tr role="row" id="jform_timings_days_row_<?php echo $num; ?>" class="timingsrow">
				<td class="text-align-left">
					<label class="checkbox">
						<input type="checkbox" name="<?php echo $field['name']; ?>[<?php echo $num;?>][week_day]" id="<?php echo $field['fldName'] . '_' . $field['id']; ?>_<?php echo $num;?>_week_day" value="<?php echo $num;?>" <?php echo $checked;?>>
						<span><?php echo $day;?></span>
					</label>
				</td>
				<td>
					<?php
					$fromFieldName = $field['name'] . '[' . $num . '][from_time]';
					$fromFieldId   = $field['id'] . '_' . $num . '_from_time';
					$fromAttribs   = 'class="w40p from_time"' . ($fullDay ? ' disabled="true"' : '');
					echo JHtml::_('select.genericlist', $timeOptions, $fromFieldName, $fromAttribs, 'id', 'title', $from, $fromFieldId);
					?>

					<?php
					$toFieldName = $field['name'] . '[' . $num . '][to_time]';
					$toFieldId   = $field['id'] . '_' . $num . '_to_time';
					$toAttribs   = 'class="w40p to_time"' . ($fullDay ? ' disabled="true"' : '');
					echo JHtml::_('select.genericlist', $timeOptions, $toFieldName, $toAttribs, 'id', 'title', $to, $toFieldId);
					?>
				</td>
				<td>
					<label>
						<?php if ($count < 1): ?>
							<button type="button" class="btn btn-primary <?php echo $field['fldName'] ?>-copy-all"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_COPY_TO_ALL');?></button>
						<?php endif; ?>
					</label>
				</td>
				<td class="text-align-center">
					<label class="checkbox">
						<input type="checkbox" class="select_24_hrs" name="<?php echo $field['name']; ?>[<?php echo $num;?>][full_day]" id="<?php echo $field['id']; ?>_<?php echo $num;?>_full_day" value="1" <?php echo $fullDay ?>>
						<span><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_STORE_TIMINGS_FULL_DAY');?></span>
					</label>
				</td>
			</tr>
			<?php
			$count++;
		}
		?>
		<tr class="sfss-blankrow hidden">
			<td colspan="4"></td>
		</tr>
		</tbody>
	</table>
</div>
