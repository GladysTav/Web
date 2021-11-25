<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('Text');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6.1
 */
class JFormFieldMapAddress extends JFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var	 string
	 */
	protected $type = 'MapAddress';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6.1
	 */
	protected function getInput()
	{
		$input = parent::getInput();

		if (JPluginHelper::isEnabled('system', 'sellacioushyperlocal'))
		{
			// Default US location for now
			$lat     = (float) $this->element['lat'] ?: '';
			$lng     = (float) $this->element['lng'] ?: '';
			$geoType = (string) $this->element['geo_type'] ?: 'google';

			if ($geoType == 'google')
			{
				$hlConfig     = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
				$hlParams     = $hlConfig->getParams();
				$googleApiKey = $hlParams->get('google_api_key', '');

				$args = array(
					"id"   => $this->id,
					"type" => $geoType,
					"zoom" => 12,
					"lat"  => $lat,
					"lng"  => $lng
				);
				$args = json_encode($args);

				JHtml::_('jquery.framework');
				JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey . '&libraries=places', false, false);
				JHtml::_('script', 'com_sellacious/field.geolocation.js', false, true);
				JHtml::_('stylesheet', 'com_sellacious/field.mapaddress.css', false, true);

				$script = <<<JS
				jQuery(document).ready(function($) {
					$(window).load(function () {
						window.{$this->id} = new JFormFieldMapAddress;
						window.{$this->id}.init({$args});
						google.maps.event.trigger(window.{$this->id}.map, 'resize');
						
						$('#trigger_{$this->id}').on('click', function() {
						    $('#{$this->id}_map').addClass('in');
						});
						
						$('.map-backdrop-{$this->id}').on('click', function() {
						  $('.mapaddress').removeClass('in');

						});
					});

				});

JS;
				JFactory::getDocument()->addScriptDeclaration($script);
				$input .= '<a id="trigger_' . $this->id . '" class="trigger-map"><i class="fa fa-map-marker"></i>Map</a>';
				$input .= '<div class="clearfix"></div>';
				$input .= '<div id="' . $this->id .'_map" class="mapaddress"></div>';
				$input .= '<div class="map-backdrop map-backdrop-' . $this->id . '"></div>';
			}
			elseif ($geoType == 'database')
			{
				$args = array(
					"id"   => $this->id,
					"type" => $geoType,
				);
				$args = json_encode($args);

				JHtml::_('jquery.framework');
				JHtml::_('script', 'com_sellacious/plugin/autocomplete/jquery.autocomplete.ui.js', false, true);
				JHtml::_('script', 'com_sellacious/field.geolocation.js', false, true);
				JHtml::_('stylesheet', 'com_sellacious/jquery.autocomplete.ui.css', false, true);

				$script = <<<JS
				jQuery(document).ready(function($) {
					$(window).load(function () {
						window.{$this->id} = new JFormFieldMapAddress;
						window.{$this->id}.init({$args});
					});
				});
JS;
				JFactory::getDocument()->addScriptDeclaration($script);
			}
		}

		return $input;
	}
}
