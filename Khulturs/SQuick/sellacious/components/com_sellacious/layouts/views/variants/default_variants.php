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

/** @var array $tplData */
list($i, $item, $isOwn, $canEditS, $canEditP) = $tplData;

/** @var  SellaciousViewProducts $this */
$s_currency   = $this->helper->currency->forSeller($item->seller_uid, 'code_3');
$multi_seller = $this->helper->config->get('multi_seller', 0);

$j  = 0;

foreach ($item->variants as $vid => $variant)
{
	$j++;
	$variant = (object) $variant;
	?>
	<tr class="variant-row" data-row="<?php echo $i ?>" data-variant="<?php echo $j ?>">
		<td style="padding: 1px;"></td>
		<td class="nowrap">
			<?php echo $this->escape($item->local_sku); ?> <strong><?php echo $this->escape($variant->local_sku); ?></strong>
		</td>
		<td class="nowrap" colspan="<?php echo 2 + ($multi_seller ? 1 : 0) ?>">
			<input type="hidden" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][seller_uid]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_seller_uid" value="<?php echo $item->seller_uid ?>">
			<input type="hidden" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][variant_id]"
				   id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_variant_id" value="<?php echo $variant->id ?>">
			<?php echo $this->escape($item->title); ?> <strong><?php echo $this->escape($variant->title); ?></strong>
		</td>
		<?php if ($this->helper->config->get('stock_management', 'product') != 'global'): ?>
            <td class="nowrap" style="width:70px;">
                <div class="controls">
                <input type="text" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][stock]"
                       id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_stock" data-field="stock" class="form-control tiny-input stock"
                       data-float="0" value="<?php echo $variant->stock ?>" title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
                </div>
            </td>
            <?php if (isset($this->headings_display['over_stock'])): ?>
                <td class="nowrap" style="width:70px;">
                    <div class="controls">
                        <input type="text" name="jform[<?php echo $i ?>][variants][<?php echo $j ?>][over_stock]"
                               id="jform_<?php echo $i ?>_variants_<?php echo $j ?>_over_stock"
                               data-field="over-stock" class="form-control tiny-input over-stock"
                               data-float="0" value="<?php echo $variant->over_stock ?>" title="" <?php echo $canEditS ? '' : ' disabled="disabled"'; ?>/>
                    </div>
                </td>
            <?php endif; ?>
		<?php endif; ?>
		<td class="center hidden-phone" style="color: #9f9f9f;">(<?php echo (int) $variant->id; ?>)</td>
	</tr>
	<?php
}
