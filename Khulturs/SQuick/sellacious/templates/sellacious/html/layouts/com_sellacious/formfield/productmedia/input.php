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

/** @var  JLayoutFile  $this */
/** @var  array  $displayData */
$field = (object) $displayData;
$edit  = 'index.php?option=com_sellacious&view=productmedia&p=' . $field->code . '&id=0';
?>
<div class="jff-productmedia-wrapper" id="<?php echo $field->id ?>_wrapper" data-item-code="<?php echo $field->code ?>">
	<div class="table-jff-product-media">
		<div class="jff-product-media-add">
			<a class="btn btn-xs btn-success"
				data-drawer-url="<?php echo htmlspecialchars($edit) ?>"><i class="fa fa-plus fa-lg"></i>
					<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_BTN_ADD_FILE_LABEL'); ?></a>
		</div>
		<div class="clearfix"></div>
		<div class="jff-productmedia-items bg-color-white">
		</div>
	</div>
</div>
