<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass  $displayData */

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

$helper    = SellaciousHelper::getInstance();
$data      = new Registry($displayData);
$variants  = $data->get('variants', array());
$productId = $data->get('product_id', 0);
$sellerUid = $data->get('seller_uid', 0);

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/plugin/serialize-object/jquery.serialize-object.min.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/plugin/jquery.iframe-drawer.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/field.variants.js', array('version' => S_VERSION_CORE, 'relative' => true));

$me = JFactory::getUser();
?>
<script>
	jQuery(function ($) {
		$(document).ready(function () {
			window.document.tabVariant = new SellaciousFieldProduct.Variant;
			window.document.tabVariant.init('#tab-variants', '<?php echo JSession::getFormToken() ?>', '<?php echo JUri::root(true) ?>');
		});
	});
</script>
<div class="pull-right margin-bottom-10 margin-top-10">
	<?php
	if ($helper->access->check('variant.create'))
	{
		$edit = 'index.php?option=com_sellacious&view=variant&id=0&product_id=' . $productId . '&seller_uid=' . $sellerUid;
		?><button type="button" class="btn btn-xs btn-success edit-variant" id="add-variant"
		          data-drawer-url="<?php echo htmlspecialchars($edit) ?>"><i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_SELLACIOUS_ADD_VARIANT'); ?></button><?php
	}
	?>
</div>

<div class="variant-drawer-backdrop"></div>
<table class="table table-bordered table-striped table-hover" id="variants-list" style="clear: none;">
	<tbody>
	<?php
	if (!empty($variants))
	{
		$prices       = $data->get('prices', array());
		$price_mods   = is_array($prices) ? $helper->core->arrayAssoc($prices, 'variant_id', 'price_mod') : array();
		$mod_percents = is_array($prices) ? $helper->core->arrayAssoc($prices, 'variant_id', 'price_mod_perc') : array();
		$product      = $helper->product->getItem($productId);

		foreach ($variants as $i => $variant)
		{
			$filter = array(
				'table_name' => 'variants',
				'record_id'  => $variant->id,
				'context'    => 'images',
			);
			$image  = $helper->media->getFieldValue($filter, 'path');

			$variant->image    = $helper->media->getURL($image, true);
			$variant->price    = ArrayHelper::getValue($price_mods, $variant->id, 0);
			$variant->price_pc = ArrayHelper::getValue($mod_percents, $variant->id, 0);

			// todo: Decouple this access check from layout, probably move to a helper function
			$isOwner        = $variant->owned_by > 0 && ($variant->owned_by == $me->get('id'));
			$allowCreate    = $helper->access->check('variant.create');
			$allowEdit      = $helper->access->check('variant.edit') || ($isOwner && $helper->access->check('variant.edit.own'));
			$allowDelete    = $helper->access->check('variant.delete') || ($isOwner && $helper->access->check('variant.delete.own'));
			$allowEditState = $helper->access->check('variant.edit.state');

			$data = new stdClass;

			$data->variant          = $variant;
			$variant->product_title = $product->title;
			$variant->product_sku   = $product->local_sku;
			$data->seller_uid       = $sellerUid;
			$data->allow_edit       = $allowEdit;
			$data->allow_create     = $allowCreate;
			$data->allow_delete     = $allowDelete;
			$data->allow_edit_state = $allowEditState;

			echo JLayoutHelper::render('com_sellacious.product.variant.row', $data);
		}
	}
	?>
	</tbody>
</table>
