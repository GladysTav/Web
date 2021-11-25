<?php
/**
 * @version     2.0.0
 * @package     Sellacious Hyperlocal Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', "https://maps.googleapis.com/maps/api/js?key={$settings->getApiKey()}&libraries=places", array('relative' => false));
JHtml::_('stylesheet', 'com_sellacious/font-awesome.css', array('relative' => true));

JHtml::_('script', 'mod_sellacious_hyperlocal/jquery-ui.min.js', array('relative' => true));
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery-ui.css', array('relative' => true));

JHtml::_('script', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.js', array('relative' => true));
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/jquery.autocomplete.ui.css', array('relative' => true));

JHtml::_('script', 'mod_sellacious_hyperlocal/module.js', array('relative' => true));
JHtml::_('script', 'mod_sellacious_hyperlocal/hyperlocal.js', array('relative' => true));
JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/default.css', array('relative' => true));

JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_NO_RESULTS_FOUND');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED');
JText::script('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED');

$moduleOpts = array(
	'google_api_key'   => $settings->getApiKey(),
	'components'       => $components,
	'autocomplete'     => $autocomplete,
	'unit_symbol'      => $unitSymbol,
	'unit_rate'        => $unitRate,
	'hyperlocal_type'  => $hyperlocalType,
	'distance_limit'   => (int) $distanceLimit,
	'distance_default' => (int) $distanceDefault,
	'distance_min'     => (int) $distanceMin,
	'distance_max'     => (int) $distanceMax,
	'latitude'         => $userState->get('lat'),
	'longitude'        => $userState->get('long'),
);
?>
<div class="ctech-wrapper mod_sellacious_hyperlocal" data-mod_sellacious_hyperlocal="<?php echo htmlspecialchars(json_encode($moduleOpts)) ?>">

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
					<?php
				}
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
					<?php
				}
			}?>
		</ul>
		<div class="select-yourloc">
			<div class="select-yourloc-box">
				<p><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_NOT_YOUR_LOCATION'); ?></p>
			</div>
		</div>
	</div>

	<div class="select-zone">
		<div>
			<div class="result_content">
				<i class="fa fa-map-marker marker-main"></i>
				<h5 class="select-zone-label"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SELECT_ZONE_LABEL'); ?></h5>
				<p><span class="select-zone-sublabel"><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_SELECT_ZONE_SUB_LABEL'); ?></span></p>
				<div class="buil_img"></div>
			</div>

			<div class="get-loc-form">

				<div class="ctech-input-group ctech-mb-3">
					<div class="ctech-input-group-append"><button class="ctech-btn ctech-btn-secondary"><i class="fa map-marker fa-map-marker"></i></button></div>
					<input type="text" class="ctech-form-control address-input" value="<?php echo $userState->get('address'); ?>"
						   placeholder="<?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_FIELD_ADDRESS_PLACEHOLDER') ?>" title="">
					<div class="ctech-input-group-append">
						<?php if ($browserDetect): ?>
							<button type="button" class="ctech-btn ctech-btn-primary btn-detect-location"><i class="fa fa-location-arrow"></i></button>
						<?php endif; ?>
						<button type="button" class="ctech-btn ctech-btn-secondary btn-reset-location"><i class="fa fa-times"></i></button>
					</div>
				</div>

				<input type="hidden" class="address-id" value="<?php echo $userState->get('id'); ?>">

				<?php if ($distanceFilter): ?>
					<div class="distance_filter">
						<div>
							<strong><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_RADIUS'); ?>:</strong>
							<span class="distance-display"></span>
						</div>
						<div class="distance_filter_slider"></div>
						<div class="distance_ranges">
							<span class="pull-left"><?php echo sprintf('%s %s', 0, $unitSymbol); ?></span>
							<span class="pull-right"><?php echo sprintf('%s %s', $distanceLimit, $unitSymbol); ?></span>
						</div>
						<div class="clearfix"></div>
					</div>
				<?php endif; ?>

			</div>

			<div class="back-loc ctech-pull-left">
				<p><i class="fa  fa-long-arrow-left"></i> <?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_BACK_TO_LOCATIONS'); ?></p>
			</div>
		</div>
	</div>
</div>
