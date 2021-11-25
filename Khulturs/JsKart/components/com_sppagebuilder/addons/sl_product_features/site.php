<?php
/**
 * @version     2.1.4
 * @package     SP Page Builder Addons for Sellacious
 *
 * @copyright   Copyright (C) 2016. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Bhavika Matariya <info@bhartiy.com> - http://www.bhartiy.com
 */

//no direct access
defined('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonSL_Product_Features extends SppagebuilderAddons
{

	public function render()
	{

		$class                   = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title                   = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector        = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$features_text_color     = (isset($this->addon->settings->features_text_color) && $this->addon->settings->features_text_color) ? $this->addon->settings->features_text_color : '0';
		$show_seperator          = (isset($this->addon->settings->show_seperator) && $this->addon->settings->show_seperator) ? $this->addon->settings->show_seperator : '0';
		$show_seperator_position = (isset($this->addon->settings->show_seperator_position) && $this->addon->settings->show_seperator_position) ? $this->addon->settings->show_seperator_position : 'Top';

		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$html    = '';

		$helper = SellaciousHelper::getInstance();

		//Options
		if ($product)
		{
			$result = $helper->product->getItem($product);

			if ($result->features)
			{
				if($features_text_color) { ?>
					<style>
						.product-features li{
							color: <?php echo $features_text_color; ?>;
						}
					</style>
				<?php }
				$seperatorposition = array($show_seperator_position);
				$seperatortop = in_array('top', $seperatorposition);
				$seperatorbottom = in_array('bottom', $seperatorposition);
				$seperatorboth = in_array('both', $seperatorposition);

				if($show_seperator && $seperatortop || $seperatorboth)
				{
					$html .= '<hr class="isolate">';
				}
				$html .= '<ul class="product-features">';
				foreach ($result->features as $feature)
				{
					$html .= '<li>' . $feature . '</li>';
				}
				$html .= '</ul>';
				if($show_seperator && $seperatorbottom || $seperatorboth)
				{
					$html .= '<hr class="isolate">';
				}
			}
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-product-features ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productstyle.css');

	}

}
