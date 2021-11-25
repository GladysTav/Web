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
JText::script('MOD_SELLACIOUS_HYPERLOCAL_MESSAGE_DETECTED_EMPTY');

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

	<div class="ctech-input-group ctech-mb-3">
		<input type="text" class="ctech-form-control address-input" value="<?php echo $userState->get('address'); ?>"
			   placeholder="<?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_FIELD_ADDRESS_PLACEHOLDER') ?>" title="">
		<div class="ctech-input-group-append">
			<?php if ($browserDetect): ?>
				<button type="button" class="ctech-btn ctech-btn-primary btn-detect-location" <?php
					echo $autoDetect ? ' data-detect="true"' : '' ?>><i class="fa fa-location-arrow"></i></button>
			<?php endif; ?>
			<button type="button" class="ctech-btn ctech-btn-secondary btn-reset-location"><i class="fa fa-times"></i></button>
		</div>
	</div>

	<input type="hidden" class="address-id" value="<?php echo $userState->get('id'); ?>">

	<?php if ($distanceFilter): ?>
		<div class="distance_filter">
			<div>
				<strong><?php echo JText::_('MOD_SELLACIOUS_HYPERLOCAL_RADIUS'); ?>: </strong>
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
