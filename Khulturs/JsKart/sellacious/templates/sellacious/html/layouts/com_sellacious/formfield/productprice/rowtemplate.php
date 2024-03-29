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

/** @var stdClass $displayData */
$field  = $displayData;
$helper = SellaciousHelper::getInstance();
$now    = JFactory::getDate()->toSql(true);
$value  = (array) $field->value;

$id     = isset($value['id']) ? $value['id'] : 0;
$cp     = isset($value['cost_price']) ? $value['cost_price'] : '0.00';
$lp     = isset($value['list_price']) ? $value['list_price'] : '0.00';
$margin = isset($value['margin']) ? $value['margin'] : '0.00';
$mar_tp = isset($value['margin_type']) ? $value['margin_type'] : 0;
$fp     = isset($value['calculated_price']) ? $value['calculated_price'] : '0.00';

$ovrprice   = isset($value['ovr_price']) ? $value['ovr_price'] : '0.00';
$seller_uid = $field->form->getValue('seller_uid');
$c_code     = $helper->currency->forSeller($seller_uid, 'code_3');
?>
<tr role="row" id="<?php echo $field->id ?>_sfpprow" class="sfpprow">
	<td style="width:120px;" class="hide-on-flat-price">
		<div class="input-group">
		<input name="<?php echo $field->name ?>[cost_price]"
			id="<?php echo $field->id ?>_cost_price"
			class="form-control required sfpprow-cp sfpprow-copy text-right" data-ctx="cost_price"
			value="<?php echo number_format($cp, 2, '.', '') ?>" title="" required />
			<span class="input-group-addon currency-code"><?php echo $c_code ?></span>
		</div>
	</td>
	<td style="width:130px;" class="hide-on-flat-price">
		<div class="input-group">
			<input name="<?php echo $field->name ?>[margin]"
				id="<?php echo $field->id ?>_margin" data-ctx="margin"
				class="form-control required sfpprow-margin sfpprow-copy  text-right"
				data-include="<?php echo $field->id ?>_margin_type"
				value="<?php echo number_format($margin, 2, '.', ''); ?>" title="" required />
			<span class="input-group-addon">
				<span class="onoffswitch">
					<input type="checkbox" name="<?php echo $field->name ?>[margin_type]"
						id="<?php echo $field->id ?>_margin_type" value="1"
						class="onoffswitch-checkbox sfpprow-margin-type"
						<?php echo $mar_tp ? 'checked' : '' ?>/>
					<label class="onoffswitch-label"
						for="<?php echo $field->id ?>_margin_type">
						<span class="onoffswitch-inner" data-swchon-text="%"
							data-swchoff-text="<?php echo $c_code ?>"></span>
						<span class="onoffswitch-switch"></span>
					</label>
				</span>
			</span>
		</div>
	</td>
	<td style="width:120px;" class="hide-on-flat-price">
		<div class="input-group">
			<input name="<?php echo $field->name ?>[list_price]"
				id="<?php echo $field->id ?>_list_price" data-ctx="list_price"
				class="form-control required sfpprow-lp sfpprow-copy  text-right"
				value="<?php echo number_format($lp, 2, '.', '') ?>" title="" required />
			<span class="input-group-addon currency-code"><?php echo $c_code ?></span>
		</div>
	</td>
	<td style="width:120px;" class="hide-on-flat-price">
		<div class="input-group">
			<input name="<?php echo $field->name ?>[calculated_price]"
				id="<?php echo $field->id ?>_calculated_price" data-ctx="calculated_price"
				class="form-control sfpprow-fp sfpprow-copy text-right" readonly
				value="<?php echo number_format($fp, 2, '.', '') ?>" title="" required />
			<span class="input-group-addon currency-code"><?php echo $c_code ?></span>
		</div>
	</td>
	<td style="width:120px;">
		<div class="input-group">
			<input name="<?php echo $field->name ?>[ovr_price]"
				id="<?php echo $field->id ?>_ovr_price" data-ctx="ovr_price"
				class="form-control required sfpprow-ovrprice sfpprow-copy  text-right"
				value="<?php echo number_format($ovrprice, 2, '.', '') ?>" title="" required />
			<span class="input-group-addon currency-code"><?php echo $c_code ?></span>
		</div>
		<input type="hidden" name="<?php echo $field->name ?>[id]"
			id="<?php echo $field->id ?>_id"
			class="form-control" value="<?php echo $id ?>" />
	</td>
</tr>
