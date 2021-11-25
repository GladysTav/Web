<?php
/**
 * @version     1.7.4
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery-ui.css', null, true);
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/default.css', null, true);

JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_NO_RESULTS_FOUND');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_GET_CURRENT_LOCATION');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_DETECTING_LOCATION');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED');

JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $params->get('google_api_key') . '&libraries=places', false, false);
JHtml::_('script', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.js', false, true);

JHtml::_('script', 'mod_sellacious_hyperlocal/default.js', false, true);

$args   = array(
	'location_field'   => 'hyperlocation',
	'location_value'   => 'hyperlocation_id',
	'geo_finder_btn'   => 'detect-location',
	'min_distance'     => $minDistance,
	'max_distance'     => $maxDistance,
	'product_distance' => $productDistance,
	'unit_rate'        => $rate,
	'params'           => $params->toArray(),
);
$args   = json_encode($args);
$script = <<<JS
		jQuery(document).ready(function($) {
			var o = new ModSellaciousHyperLocal;
			o.setup({$args});
			o.init();
			o.geolocate({$hyperlocal});
			
			var unitRate = {$rate};
			
			$('.btn-filter_shippable').prop('onclick',null).off('click');
			$('.btn-filter_shippable').on('click', function (e) {
				var value = $('#filter_shippable_text').val();
				var btn   = $(this);
				
				o.setShippableFilter(value, function(){
					btn.closest('form').submit();
				});
				
				return false;
			});
			
			$('.btn-filter_shop_location').prop('onclick',null).off('click');
			$('.btn-filter_shop_location').on('click', function (e) {
				var value = $('#filter_store_location_custom_text').val();
				var btn   = $(this);
				
				o.setLocationFilter(value, function(){
					btn.closest('form').submit();
				});
				
				return false;
			});
			
			$('#reset-location').on('click', function(e) {
			  e.preventDefault();
			  
			  $('#hyperlocation').val('');
			  $('#hyperlocation_id').val('');
			  
			  o.resetAddress(function() {
			    window.location.reload();
			  });
			});
			
			$('#update-radius').on('click', function(e){
				e.preventDefault();
				
				var lat  = $('#hyperlocation_lat').val();
				var long = $('#hyperlocation_long').val();
				var minMetres  = parseInt($('#hyperlocation_min_distance').val());
				var maxMetres  = parseInt($('#hyperlocation_max_distance').val());
				var min = $( "#distance_filter_{$module->id}" ).slider( "values", 0 );
				var max = $( "#distance_filter_{$module->id}" ).slider( "values", 1 );
				
				o.setRadiusRange(lat, long, min, max, minMetres, maxMetres);
			});
			
			$( "#distance_filter_{$module->id}" ).slider({
			  range: true,
			  min: {$distance_min},
			  max: {$distance_max},
			  values: [ parseInt({$min_radius}), parseInt({$max_radius}) ],
			  slide: function( event, ui ) {
				$('#distance_min_display_{$module->id}').text(ui.values[0]);
				$('#distance_max_display_{$module->id}').text(ui.values[1]);
				$('#hyperlocation_min_distance').val(ui.values[0] * unitRate);
				$('#hyperlocation_max_distance').val(ui.values[1] * unitRate);
			  }
			});
			
			$('#distance_min_display_{$module->id}').text($( "#distance_filter_{$module->id}" ).slider( "values", 0 ));
			$('#distance_max_display_{$module->id}').text($( "#distance_filter_{$module->id}" ).slider( "values", 1 ));
			
			// Hyperlocal Address
			$(".select-yourloc").on('click', function () {
				$(".hyperlocal-countries").addClass("hidden");
				$(".select-zone").removeClass('hidden');
			});
			$('.back-loc').on('click', function () {
				$(".hyperlocal-countries").removeClass("hidden");
				$(".select-zone").addClass('hidden');
			});
			
			$('.country-detail-li a').on('click', function(e){
				e.preventDefault();
				
				var location = $(this).data('location');
				var lat = $(this).data('lat');
				var lng = $(this).data('lng');
				
				if (o.options.params.address_matching == 2) {
					o.setCustomAddress(location, lat, lng, function(){
						window.location.reload();
					});
				} else {
					o.setCustomAddress(location, lat, lng, o.setBounds);
				}
			});
		});
JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>

<ul class="hyperlocal-menu">
	<li class="country-detail-li-main">
        <span class="country-detail-li-main">
			<img src="<?php echo JUri::root() . 'images/city.png'; ?>" width="30px">
			<span title="<?php echo isset($location['address']) ? $location['address'] : ''; ?>"
				  class="hasTooltip main-add"><?php echo isset($location['address']) ? $location['address'] : ''; ?></span>
			<small class="fa fa-caret-down"> </small>
        </span>

		<div class="hyperlocal-countries">
			<ul class="hyperlocal-countries-ul">
				<?php
				if ($display_location_via == 'latitude_longitude')
				{
					$places = $adddetails_one_lat ?: array();

					foreach ($places as $place)
					{
						$countryImage = $place->country_image ?: JUri::root() . 'images/city.png';
						?>
						<li class="country-detail-li">
							<a href="#" data-location="<?php echo $place->location_state; ?>" data-lat="<?php echo $place->latitude?>" data-lng="<?php echo $place->longitude?>">
								<span class="image-country" style="background-image:url(<?php echo($countryImage); ?>)"> </span>
								<span class="country-name">
									<?php echo $place->location_state; ?>
								</span>
							</a>
						</li>
					<?php }
				}
				else
				{
					$places = $adddetails_one_location ?: array();

					foreach ($places as $place)
					{
						$countryImage  = $place->country_image ?: JUri::root() . 'images/city.png';
						$placeLocation = array();

						if (!empty($place->location_city)) $placeLocation[] = $place->location_city;
						if (!empty($place->location_state)) $placeLocation[] = $place->location_state;
						if (!empty($place->location_country)) $placeLocation[] = $place->location_country;
						if (!empty($place->location_zip)) $placeLocation[] = $place->location_zip;
						?>
						<li class="country-detail-li">
							<a href="#" data-location="<?php echo implode(', ', $placeLocation); ?>">
								<span class="image-country" style="background-image:url(<?php echo($countryImage); ?>)"> </span>
								<span class="country-name">
									<?php
									switch ($show_location_by_style_one)
									{
										case 'country':
											echo $place->location_country;
											break;
										case 'state':
											echo $place->location_state;
											break;
										case 'city':
											echo $place->location_city;
											break;
										case 'zip':
											echo $place->location_zip;
											break;
									}
									?>
								</span>
							</a>
						</li>
					<?php }
				}?>
			</ul>
			<div class="select-yourloc">
				<div class="select-yourloc-box">
					<p><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_NOT_YOUR_LOCATION'); ?></p>
				</div>
			</div>
		</div>

		<div class="select-zone hidden">
			<div>
				<div class="result_content">
					<i class="fa fa-map-marker marker-main"></i>
					<h5 class="select-zone-label"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SELECT_ZONE_LABEL'); ?></h5>
					<p><span class="select-zone-sublabel"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SELECT_ZONE_SUB_LABEL'); ?></span></p>
					<div class="buil_img"></div>
				</div>
				<div class="get-loc-form mod_sellacious_hyperlocation">
					<i class="fa map-marker fa-map-marker"></i>
					<input class="input-hyper-add-loc" id="hyperlocation" name="hyperlocation" placeholder="Enter your address" type="text"
						   data-autofill-components="<?php echo implode(',', $components); ?>"
						   value="<?php echo isset($location['address']) ? $location['address'] : ''; ?>">
					<input type="hidden" id="hyperlocation_id" name="hyperlocation_id"
						   value="<?php echo isset($location['id']) ? $location['id'] : ''; ?>">
					<input type="hidden" id="hyperlocation_lat" name="hyperlocation_lat" value="<?php echo $location->get('lat', 0); ?>">
					<input type="hidden" id="hyperlocation_long" name="hyperlocation_long" value="<?php echo $location->get('long', 0); ?>">
					<input type="hidden" id="hyperlocation_min_distance" name="hyperlocation_min_distance" value="<?php echo $minDistance; ?>">
					<input type="hidden" id="hyperlocation_max_distance" name="hyperlocation_max_distance" value="<?php echo $maxDistance; ?>">


					<?php if ($distance_filter): ?>
						<div class="distance_filter">
							<div>
								<b><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_RADIUS'); ?>:</b>
								<span id="distance_min_display_<?php echo $module->id; ?>"></span>
								<span class="dist_div"> - </span>
								<span id="distance_max_display_<?php echo $module->id; ?>"></span>
								<span class="distance_unit"><?php echo $distance_unit_value; ?></span>
							</div>
							<div id="distance_filter_<?php echo $module->id; ?>"></div>
							<div class="distance_ranges">
								<span class="pull-left"><?php echo $distance_min . ' ' . $distance_unit_value; ?></span>
								<span class="pull-right"><?php echo $distance_max . ' ' . $distance_unit_value; ?></span>
							</div>
							<div class="clearfix"></div>
							<button type="button" class="btn btn-primary"
									id="update-radius"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_UPDATE_RADIUS'); ?></button>
						</div>
					<?php endif; ?>

					<?php if ($browser_detect): ?>
						<button type="button" class="search-location btn btn-xs btn-primary"
								id="detect-location"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SEARCH'); ?></button>
					<?php else: ?>
						<button type="button" class="btn btn-xs btn-primary"
								id="reset-location"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SEARCH_RESET'); ?></button>
					<?php endif; ?>
				</div>
				<div class="back-loc">
					<p><i class="fa  fa-long-arrow-left"></i> <?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_BACK_TO_LOCATIONS'); ?></p>
				</div>
			</div>
		</div>
	</li>
</ul>
