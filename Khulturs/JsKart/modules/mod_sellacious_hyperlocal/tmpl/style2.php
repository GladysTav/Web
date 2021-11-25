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

if($display_location_via == 'latitude_longitude')
{
	$places = $adddetails_two_lat ?: array();
}
else
{
	$places = $adddetails_two_location ?: array();
}
?>
<div class="ctech-wrapper mod_sellacious_hyperlocal" data-mod_sellacious_hyperlocal="<?php echo htmlspecialchars(json_encode($moduleOpts)) ?>">

<ul class="hyperlocal-menu">
    <li class="country-detail-li-main ui_two">
	    <?php if ($places): ?>
        <span>
            <span class="main-img-loc"><img src="<?php echo JUri::root() . 'images/city.png'; ?>"></span>
            <span class="main-add" ><?php echo isset($location['address']) ? $location['address'] : '';?> <small class="fa fa-caret-down"> </small></span>
            <?php if ($browser_detect): ?>
                <button type="button" class="btn-detect-location"></button>
            <?php else: ?>
                <button type="button" class="btn" id="reset-location"></button>
            <?php endif; ?>
        </span>

        <div class="hyperlocal-countries">
            <ul class="hyperlocal-countries-ul">
                <?php
                if ($display_location_via == 'latitude_longitude')
                {
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
									switch ($show_location_by_style_two)
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
				}
                ?>
            </ul>
        </div>
		<?php elseif (isset($location['address'])): ?>
			<span>
            <span class="main-img-loc"><img src="<?php echo JUri::root() . 'images/city.png'; ?>"></span>
            <span class="main-add" ><?php echo isset($location['address']) ? $location['address'] : '';?></span>
            <?php if ($browser_detect): ?>
				<button type="button" class="btn-detect-location"></button>
            <?php else: ?>
				<button type="button" class="btn" id="reset-location"></button>
            <?php endif; ?>
        </span>
		<?php else: ?>
			<?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_NO_LOCATIONS_FOUND');?>
		<?php endif; ?>
    </li>
</ul>
</div>
