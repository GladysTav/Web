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

use Sellacious\Product;
use Joomla\Utilities\ArrayHelper;

class SppagebuilderAddonSL_Product_Attributes extends SppagebuilderAddons
{

	public function render()
	{

		$class            = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title            = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$app     = JFactory::getApplication();
		$input  = $app->input;
		$product = $input->getInt('product');
		$variant = $input->getInt('v');
		$seller  = $input->getInt('s');
		$pCode   = $input->getVar('p');
		$html    = '';
		$helper  = SellaciousHelper::getInstance();

		if ($product)
		{

			$choices = $this->getVariantChoices();
			if (!$choices)
			{
				return;
			}

			JHtml::_('script', 'com_sellacious/fe.view.product.js', false, true);

			ob_start();
			?>
			<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" id="varForm" name="varForm">

				<div class="variant-picker ctech-wrapper">
					<?php foreach ($choices as $choice): ?>
						<div class="variant-choice">
							<h5><?php echo $choice->title ?></h5>

							<div class="radio">
								<?php
								foreach ($choice->options as $option):
									$o_value  = htmlspecialchars($option);
									$o_text   = $helper->field->renderValue($option, $choice->type);

									$selected = $choice->selected == $option ? ' checked' : '';

									if (in_array($option, $choice->available)):
										$availability = 'available-option';
										$disabled     = '';
									else:
										$availability = 'unavailable-option';
										$disabled     = 'disabled';
									endif;

									if($choice->type == 'color'): ?>
										<label class="colors-option <?php echo $availability; echo ($selected) ? ' selected' : ''; ?>">
											<input type="radio" class="variant_spec" name="jform[variant_spec][<?php echo $choice->id ?>]"
												   value="<?php echo $o_value ?>" <?php echo $selected . ' ' . $disabled ?>>
											<span class="variant_spec" style="background: <?php echo $option ?>"></span>
										</label>
										<?php
									else: ?>
										<label class="varaint-options ctech-bg-primary ctech-border-primary <?php echo $availability; echo ($selected) ? ' selected' : ''; ?>">
											<input type="radio" class="variant_spec" name="jform[variant_spec][<?php echo $choice->id ?>]"
												   value="<?php echo $o_value ?>" <?php echo $selected . ' ' . $disabled ?>><?php echo $o_text ?>
										</label><?php
									endif; ?>
									<?php
								endforeach;
								?>
							</div>
						</div>
					<?php endforeach; ?>
					<div class="clearfix"></div>
				</div>

				<input type="hidden" name="option" value="com_sellacious"/>
				<input type="hidden" name="view" value="product"/>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="p" value="<?php echo $pCode; ?>"/>

				<?php echo JHtml::_('form.token'); ?>
			</form>
			<?php

			$html	.= ob_get_clean();
			$html	.= '<div class="clearfix"></div>';
		}

		//Output
		if ($html)
		{
			$output = '<div class="sppb-addon sppb-addon-category-desc ' . $class . '">';
			$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= $html;
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	/**
	 * Get choices of the variants options to be selected by the customer
	 *
	 * @return  stdClass[]
	 */
	public function getVariantChoices()
	{
		$helper  = SellaciousHelper::getInstance();
		$app     = JFactory::getApplication();
		$jInput  = $app->input;
		$product = $jInput->getInt('product');
		$variant = $jInput->getInt('v');
		$seller  = $jInput->getInt('s');

		if (!$helper->config->get('multi_variant', 0))
		{
			return array();
		}

		$specLists   = array();
		$product_id  = $product;
		$variant_id  = $variant;
		$variant_ids = $helper->variant->loadColumn(array('list.select' => 'a.id', 'product_id' => $product_id));

		// Preload product fields for the getSpecifications call to save repetitive evaluating inside it.
		$vFields   = $helper->product->getFields($product_id, array('variant'));
		$specs     = $helper->variant->getProductSpecifications($product_id, $vFields, false);
		$specsFlat = array();

		foreach ($specs as $specKey => $specValue)
		{
			if (is_array($specValue))
			{
				// Use first value of a multivalued spec, is this ok?
				$specsFlat[$specKey] = reset($specValue);
			}
			else
			{
				$specsFlat[$specKey] = $specValue;
			}
		}

		$specLists[0] = $specsFlat;

		foreach ($variant_ids as $vid)
		{
			$specs     = $helper->variant->getSpecifications($vid, $vFields, false);
			$specsFlat = array();

			foreach ($specs as $specKey => $specValue)
			{
				if (is_array($specValue))
				{
					// Use first value of a multivalued spec, is this ok?
					$specsFlat[$specKey] = reset($specValue);
				}
				else
				{
					$specsFlat[$specKey] = $specValue;
				}
			}

			$specLists[$vid] = $specsFlat;
		}

		$allSpecs = array();
		$vfTables = array('products' => $product_id, 'variants' => $variant_ids);

		foreach ($vFields as $field)
		{
			$object = new stdClass;

			$object->id      = $field->id;
			$object->type    = $field->type;
			$object->group   = $field->group;
			$object->title   = $field->title;
			$object->options = $helper->field->getFilterChoices($field, $vfTables);

			// Skip this variant field completely if there are no available choices
			if (count($object->options))
			{
				$values = array();

				foreach ($specLists as $vid => $specFlat)
				{
					if ($value = ArrayHelper::getValue($specFlat, $field->id))
					{
						$values[$vid] = $value;
					}
				}

				$object->available   = array_unique($values);
				$object->unavailable = array_unique(array_diff($object->options, $object->available));
				$object->selected    = ArrayHelper::getValue($values, (int) $variant_id);

				$allSpecs[$field->id] = $object;
			}
		}

		return $allSpecs;
	}

	public function stylesheets()
	{
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/sellacious/sl-productattributes.css');

	}
}
