<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  object $displayData */
$arg = $displayData;

/** @var  stdClass $variant */
$variant    = $arg->variant;
$seller_uid = $arg->seller_uid;

$me         = JFactory::getUser();
$helper     = SellaciousHelper::getInstance();
$full_title = $variant->product_title . '-' . $variant->title;
$full_sku   = $variant->product_sku . '-' . $variant->local_sku;
$canChange  = isset($arg->allow_edit_state) && $arg->allow_edit_state;
?>
<tr id="variant-row-<?php echo $variant->id ?>" class="variant-row">
	<td class="nowrap center" style="width: 10px;">
		<span class="btn-round">
			<a class="btn btn-micro change-state-variant active hasTooltip <?php echo (!$canChange) ? 'disabled' : '';?>"
				data-id="<?php echo $variant->id ?>"
				data-state="<?php echo $variant->state; ?>"
				href="javascript:void(0);"
				title="<?php echo $variant->state == 1 ? 'Unpublish Item' : 'Publish Item'; ?>">
				<span class="icon-<?php echo $variant->state == 1 ? 'publish' : 'unpublish'; ?>"></span>
			</a>
		</span>
	</td>
	<td style="width:50px; padding:1px;" class="image-box">
		<img class="image-small" src="<?php echo $variant->image ?>" />
		<img class="image-large" src="<?php echo $variant->image ?>" />
	</td>
	<td class="v-top">
		<div class="pull-right">
			<?php
			if (isset($arg->allow_edit) && $arg->allow_edit)
			{
				$edit = 'index.php?option=com_sellacious&view=variant&id=' . $variant->id . '&product_id=' . $variant->product_id . '&seller_uid=' . $seller_uid;
				?><button type="button" class="btn btn-xs btn-success edit-variant" data-drawer-url="<?php echo htmlspecialchars($edit) ?>"><i class="fa fa-edit"></i> Edit</button><?php
			}

			if (isset($arg->allow_delete) && $arg->allow_delete)
			{
				?><button type="button" class="btn btn-xs btn-danger delete-variant"
				data-id="<?php echo $variant->id ?>"><i class="fa fa-times"></i> Delete</button><?php
			}
			?>
		</div>
		<div class="h2 pull-left">
			<strong><?php echo htmlspecialchars($full_title) ?></strong> (<?php echo $full_sku ?>)
			&nbsp;<small>(<?php echo $helper->product->getCode($variant->product_id, $variant->id, $seller_uid); ?>)</small>
		</div>
		<div class="clearfix"></div>
		<div class="variant-specs">
			<?php
			$span = '<span style="margin-right: 15px;"><b>%s</b>: %s</span>';

			foreach ($variant->fields as $field)
			{
				$value = $helper->field->renderValue($field->field_value, $field->field_type, $field);

				if ($value)
				{
					echo sprintf($span, $field->field_title, $value);
				}
			}
			?>
		</div>
	</td>
	<?php
	if ($seller_uid && ($helper->access->check('product.edit.seller') || ($helper->access->check('product.edit.seller.own') && $seller_uid == $me->id)))
	{
		?>
		<td class="v-middle nowrap text-right" style="width:70px;">
			<?php
			$c_code = $helper->currency->forSeller($seller_uid, 'code_3');
			$amount = number_format($variant->price, 2, '.', '');

			echo $variant->price_pc ? "$amount %" : "$amount $c_code";
			?>
		</td>
		<?php
	}
	?>
</tr>
