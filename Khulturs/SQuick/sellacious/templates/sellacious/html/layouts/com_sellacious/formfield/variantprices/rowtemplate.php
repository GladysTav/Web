<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  object  $displayData */
$formfield = $displayData;

/** @var  stdClass  $variant */
$variant = $formfield->variant;

$me         = JFactory::getUser();
$helper     = SellaciousHelper::getInstance();
$full_title = $variant->product_title . ' - ' . $variant->title;
$full_sku   = $variant->product_sku . ' - ' . $variant->local_sku;
?>
<tr id="<?php echo $formfield->id ?>_variant-row-<?php echo $variant->id ?>" class="variant-row">
	<td class="v-middle variant-summary">
		<strong><?php echo htmlspecialchars($full_title) ?> </strong>
		<small style="color:#8d8d8d;">(<?php echo $full_sku ?>)</small>
		<br>
		<div class="variant-specs text-small">
			<?php
			$span = '<span><label>%s</label>: %s</span>';

			foreach ($variant->fields as $fi => $field)
			{
				$value = $helper->field->renderValue($field->field_value, $field->field_type, $field);

				if ($value)
				{
					echo sprintf($span, $field->field_title, $value);

					if ($fi >= 2)
					{
						echo '&hellip;';
						break;
					}
				}
			}
			?>
		</div>
	</td>
	<td class="v-middle" style="width:160px;">
		<input type="hidden" name="<?php echo $formfield->name ?>[<?php echo $variant->id ?>][variant_id]"
			id="<?php echo $formfield->id ?>_<?php echo $variant->id ?>_variant_id" value="<?php echo $variant->id ?>"/>
		<div class="input-group variant_price_mod">
			<?php $c_code = $helper->currency->forSeller($variant->seller_uid, 'code_3') ?>
			<input name="<?php echo $formfield->name ?>[<?php echo $variant->id ?>][price_mod]" class="form-control required"
				value="<?php echo $variant->price_mod ?>" title=""/>
			<span class="input-group-addon">
				<span class="onoffswitch">
					<input type="checkbox" name="<?php echo $formfield->name ?>[<?php echo $variant->id ?>][price_mod_perc]"
						id="<?php echo $formfield->id ?>_<?php echo $variant->id ?>_price_mod_perc"
						class="onoffswitch-checkbox sfpprow-margin-type" value="1"
						<?php echo $variant->price_mod_perc ? 'checked' : ''; ?>/>
					<label class="onoffswitch-label"
						for="<?php echo $formfield->id ?>_<?php echo $variant->id ?>_price_mod_perc">
						<span class="onoffswitch-inner" data-swchon-text="%" data-swchoff-text="<?php echo $c_code ?>"></span>
						<span class="onoffswitch-switch"></span>
					</label>
				</span>
			</span>
		</div>
	</td>
</tr>
