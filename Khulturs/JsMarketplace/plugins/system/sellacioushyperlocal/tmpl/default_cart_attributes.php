<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die('Restricted access');

$item          = $this->item;
$params        = $this->params;
$sellerUid     = $item->get('seller_uid', 0);
$productId     = $item->get('id', 0);
$today_until   = $this->today_until;
$today         = $this->today;
$todaySlots    = $this->today_slots;
$dates         = $this->dates;
$selectedDate  = $this->selected_date;
$disabledDates = $this->disabled_dates;
$disabledDays  = $this->disabled_days;
$maxDate       = $this->max_date;

$timeSelection = $params->get('delivery_time_selection', 0);

$format     = 'YYYY-MM-DD';
$sideBySide = 'false';

if ($timeSelection == 3)
{
	$format     = 'YYYY-MM-DD HH:mm A';
	$sideBySide = 'true';
}

JHtml::_('jquery.framework');
JHtml::_('bootstrap.loadCss');

JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/glyphicons.css', null, true);
JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.css', null, true);
JHtml::_('stylesheet', 'plg_system_sellacioushyperlocal/layout.cart_attributes.css', null, true);
JHtml::_('script', 'plg_system_sellacioushyperlocal/moment-with-locales.js', false, true);
JHtml::_('script', 'plg_system_sellacioushyperlocal/bootstrap-datetimepicker.js', false, true);
JHtml::_('script', 'plg_system_sellacioushyperlocal/layout.cart_attributes.js', false, true);

JText::script("PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR");

$minDate = $selectedDate ? "minDate: '" . $selectedDate . "'," : "";

$doc = JFactory::getDocument();
$js = <<<JS
	jQuery(document).ready(function($) {
		var dtpAjax = null;
		
		$('#delivery_date_dtp').datetimepicker({
		    format: '{$format}',
		    {$minDate}
		    maxDate: '{$maxDate}',
		    disabledDates: {$disabledDates},
		    daysOfWeekDisabled: {$disabledDays},
		    sideBySide: {$sideBySide},
		    showClose: true,
		    toolbarPlacement: 'top',
		}).on('dp.change', function (e) {
			var date = $('#delivery_date').val();
			
			var data = {
				option: 'com_ajax',
				plugin: 'sellacioushyperlocal',
				method: 'onAjaxGetSlots',
				slot_date: date,
				seller_uid: '{$sellerUid}',
				product_id: '{$productId}',
				format: 'json'
			};
			
		    var paths = Joomla.getOptions('system.paths', {});
			var base  = paths.root || '';
	
			if (dtpAjax) dtpAjax.abort();
	
			dtpAjax = $.ajax({
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
						$('.item_slot_picker').html(response.data.html);
						$('#delivery_slot').select2();
					}
					
					var formatted_date = response.data.formatted_date;
					
					if ($('.btn-date[data-delivery-date="' + formatted_date + '"]').length) {
						$('.btn-date').removeClass('active');
						$('.btn-date[data-delivery-date="' + formatted_date + '"]').toggleClass('active');
						$('#delivery_date_dtp .input-group-addon').html(Joomla.JText._('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR'));
					} else if ($('.datepicker[data-delivery-date="' + formatted_date + '"]').length) {
						$('.datepicker .input-group-addon').removeClass('active');
						$('.datepicker[data-delivery-date="' + formatted_date + '"] .input-group-addon').toggleClass('active');
						$('#delivery_date_dtp .input-group-addon').html(Joomla.JText._('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR'));
					} else {
						var dt = response.data.date;
						$('.btn-date').removeClass('active');
						$('#delivery_date_dtp .input-group-addon').html(dt.weekday + '<br>' + dt.day).addClass('active');
					}
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
				console.log(jqXHR.responseText);
			});
	    }).on('dp.hide', function() {
			var date = $(this).data('delivery-date');
			$('.datepicker .input-group-addon').removeClass('active');
			$(this).find('.input-group-addon').toggleClass('active');
		});
		
		$('#delivery_date_dtp').trigger('dp.change');
	});
JS;
$doc->addScriptDeclaration($js);
?>
<div class="item_slot_section">
	<div class="clearfix"></div>
	<h4><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_SELECT_DELIVERY_DATE');?></h4>
	<ul class="pick_date">
		<?php
		if ($timeSelection == 3):
			$slotDate = $today->format('Y-m-d', true);
			$script = <<<JS
				jQuery(document).ready(function($) {
					$('.todaypicker').datetimepicker({
						format: 'LT',
						defaultDate: new Date('{$slotDate}'),
						showClose: true,
		                toolbarPlacement: 'top',
					}).on('dp.change', function (e) {
						$('#delivery_date').val(e.date.format('YYYY-MM-DD HH:mm:ss'));
					}).on('dp.hide', function() {
						var date = $(this).data('delivery-date');
						$('.datepicker .input-group-addon').removeClass('active');
						$('.datepicker[data-delivery-date="' + date + '"] .input-group-addon').toggleClass('active');
					});
				});
JS;
			$doc->addScriptDeclaration($script);
			?>
			<li>
				<div class='input-group date datepicker todaypicker' data-delivery-date="<?php echo $slotDate; ?>">
					<input <?php echo empty($todaySlots) ? "disabled" : ""; ?> type="hidden" class="w50p delivery_today" name="delivery_today" id="delivery_today">
					<span class="btn btn-date input-group-addon">
						<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_TODAY');?>
						<br>
						<?php echo $today->format('F d', true);?>
					</span>
				</div>
			</li>
			<?php
			foreach($dates as $key => $date):
				$slotDate = $date['date']->format('Y-m-d', true);
				$script = <<<JS
					jQuery(document).ready(function($) {
						$('.daypicker_{$key}').datetimepicker({
							format: 'LT',
							defaultDate: new Date('{$slotDate}'),
							showClose: true,
		                    toolbarPlacement: 'top',
						}).on('dp.change', function (e) {
							var date = $(this).data('delivery-date');
							$('#delivery_date').val(date + ' ' + e.date.format('HH:mm:ss'));
						}).on('dp.show', function() {
							var time = $(this).find('input').val();
							var date = $(this).data('delivery-date');
							
							$('#delivery_date').val(date + ' ' + time);
						}).on('dp.hide', function() {
							var date = $(this).data('delivery-date');
							$('.datepicker .input-group-addon').removeClass('active');
							$('.datepicker[data-delivery-date="' + date + '"] .input-group-addon').toggleClass('active');
						});
					});
JS;
				$doc->addScriptDeclaration($script);
				?>
				<li>
					<div class='input-group date datepicker daypicker_<?php echo $key?>' data-delivery-date="<?php echo $date['date']->format('Y-m-d', true); ?>">
						<input <?php echo empty($date['slots']) ? "disabled" : ""; ?> type="hidden" class="w50p delivery_day_<?php echo $key?>" name="delivery_day_<?php echo $key?>" id="delivery_day_<?php echo $key?>">
						<span class="btn btn-date input-group-addon">
							<?php echo $date['date']->format('D', true);?>
							<br>
							<?php echo $date['date']->format('F d', true);?>
						</span>
					</div>
				</li>
		<?php endforeach;
		else:
			?>
			<li>
				<button class="btn btn-date" type="button" <?php echo empty($todaySlots) ? "disabled" : ""; ?> data-delivery-date="<?php echo $today->format('Y-m-d', true); ?>">
					<span class="weekday"><?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_TODAY');?></span>
					<span class="date"><?php echo $today->format('F d', true);?></span>
				</button>
			</li>
			<?php
			foreach($dates as $date):?>
				<li>
					<button class="btn btn-date" type="button" <?php echo empty($date['slots']) ? "disabled" : ""; ?> data-delivery-date="<?php echo $date['date']->format('Y-m-d', true); ?>">
						<span class="weekday"><?php echo $date['date']->format('D', true);?></span>
						<span class="date"><?php echo $date['date']->format('F d', true);?></span>
					</button>
				</li>
			<?php endforeach;?>
		<?php endif;?>
		<li>
			<div class='input-group date datepicker' id='delivery_date_dtp'>
				<input type="hidden" class="w50p delivery_date" name="delivery_date" id="delivery_date">
				<span class="btn btn-date input-group-addon">
					<?php echo JText::_('PLG_SYSTEM_SELLACIOUSHYPERLOCAL_CALENDAR');?>
				</span>
			</div>
		</li>
	</ul>

	<div class="item_slot_picker"></div>
	<?php if ($today_until): ?>
	<h6 class="today_until"><?php echo $today_until; ?></h6>
	<?php endif;?>
</div>

