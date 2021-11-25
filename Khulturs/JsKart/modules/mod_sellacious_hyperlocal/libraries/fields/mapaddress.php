<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Sellacious\Config\ConfigHelper;

JFormHelper::loadFieldClass('Text');

/**
 * Form Field class for address picker from map
 *
 * @since   1.6.1
 */
class JFormFieldMapAddress extends JFormFieldText
{
	/**
	 * The field type
	 *
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $type = 'MapAddress';

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.1
	 */
	protected function getInput()
	{
		$config = ConfigHelper::getInstance('mod_sellacious_hyperlocal');
		$apiKey = $config->get('google_api_key');

		if (!$apiKey)
		{
			return parent::getInput();
		}

		parse_str((string) $this->element['field_map'], $map);

		if (is_array($map))
		{
			foreach ($map as $i => $value)
			{
				$map[$i] = $this->getId(null, $value);
			}
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', "https://maps.googleapis.com/maps/api/js?key={$apiKey}&libraries=places", array('relative' => false));

		JHtml::_('script', 'mod_sellacious_hyperlocal/field.mapaddress.js', array('relative' => true));
		JHtml::_('stylesheet', 'mod_sellacious_hyperlocal/field.mapaddress.css', array('relative' => true));

		$sb    = '';
		$opts  = array('zoom' => 17, 'map' => $map);
		$dt    = htmlspecialchars(json_encode($opts));
		$html  = '<div data-field_mapaddress="%s">' .
				'	%s<a class="fma-trigger"><i class="fa fa-map-marker"></i>Map</a>' .
				'	<div class="clearfix"></div>%s' .
				'	<div class="fma-frame"></div><div class="fma-backdrop"></div>' .
				'</div>';

		if ((string) $this->element['search_box'] === 'true')
		{
			$sb = '<input type="text" id="%s" class="inputbox medium-input fma-input fma-search-box" placeholder="%s" style="margin-bottom: 5px;"/>';
			$sb = sprintf($sb, $this->getId(null, 'store_address_input'), JText::_('MOD_SELLACIOUS_HYPERLOCAL_FILED_SEARCH_ADDRESS_LOCATION'));
		}
		else
		{
			$this->class .= ' fma-input';
		}

		$input = parent::getInput();

		return sprintf($html, $dt, $sb ?: $input, $sb ? $input : '');
	}
}
