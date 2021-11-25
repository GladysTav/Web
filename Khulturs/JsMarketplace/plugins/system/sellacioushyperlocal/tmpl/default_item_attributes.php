<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/** @var array $displayData */
$item   = $displayData['item'];
$params = $displayData['params'];

$timeSelection = $params->get('delivery_time_selection', 0);
$code          = strtolower($item->code);
$sellerUid     = (int) $item->seller_uid;
$productId     = (int) $item->id;
$selectedDate  = $displayData['selected_date'];
$disabledDates = $displayData['disabled_dates'];
$disabledDays  = $displayData['disabled_days'];
$maxDate       = $displayData['max_date'];
$prefix        = $displayData['context_prefix'];
$inputId       = $displayData['date_input_id'];
$pickerId      = $displayData['picker_id'];
$slotPickerId  = $displayData['slot_picker_id'];
$options       = new Registry($displayData['options']);

$format       = 'YYYY-MM-DD';
$sideBySide   = 'false';
$widgetParent = "null";

if ($timeSelection == 3)
{
	$format     = 'YYYY-MM-DD HH:mm A';
	$sideBySide = 'true';
}

if ($prefix != '' && $options->get('layout') == 'carousel' && $timeSelection == 3)
{
	$widgetParent = "'body'";
}

JText::script("PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR");

$minDate = $selectedDate ? "minDate: '" . $selectedDate . "'," : "";

?>
<script>
	jQuery(document).ready(function($) {
		$('#<?php echo $pickerId ?>').datetimepicker({
		    format: '<?php echo $format ?>',
		    <?php echo $minDate ?>
		    maxDate: '<?php echo $maxDate ?>',
		    disabledDates: <?php echo $disabledDates ?>,
		    daysOfWeekDisabled: <?php echo $disabledDays ?>,
		    sideBySide: <?php echo $sideBySide ?>,
		    widgetParent: <?php echo $widgetParent ?>,
		    showClose: true,
		    toolbarPlacement: 'top',
		}).on('dp.change', function (e) {
			var date = $('#<?php echo $inputId ?>').val();

			var data = {
				option: 'com_ajax',
				plugin: 'sellacioushyperlocal',
				method: 'onAjaxGetSlots',
				slot_date: date,
				seller_uid: '<?php echo $sellerUid ?>',
				product_id: '<?php echo $productId ?>',
				code: '<?php echo $item->code ?>',
				format: 'json'
			};

			var paths = Joomla.getOptions('system.paths', {});
			var base  = paths.root || '';

			$.ajax({
				url: base + '/index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success == true) {
					if (response.data.html != "") {
						$('#<?php echo $slotPickerId ?>').html(response.data.html);
						$('#<?php echo $slotPickerId ?> select').select2();
					}

					if ($('.btn-date[data-delivery-date="' + date + '"]').length) {
						$('#<?php echo $pickerId ?> .input-group-addon').html(Joomla.JText._('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR'));
					} else {
						var dt = response.data.date;
						$('#<?php echo $pickerId ?> .input-group-addon').html(dt.weekday + '<br>' + dt.day).addClass('active');
					}
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
	    });

		$('#<?php echo $pickerId ?>').trigger('dp.change');
	});
</script>

<div class="slot_section">
	<ul class="pick_date">
		<li>
			<div class='input-group date datepicker' id='<?php echo $pickerId ?>'>
				<input type="hidden" class="w50p delivery_date" name="<?php echo $inputId ?>" id="<?php echo $inputId ?>">
				<span class="btn btn-date input-group-addon">
					<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR'); ?>
				</span>
			</div>
		</li>
	</ul>

	<div class="slot_picker" id="<?php echo $slotPickerId ?>"></div>
</div>
