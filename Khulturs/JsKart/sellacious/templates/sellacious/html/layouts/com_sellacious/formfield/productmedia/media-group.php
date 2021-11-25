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

/** @var  stdClass  $displayData */
$media = $displayData->media;
$field = $displayData->field;
$linkM = JUri::root() . 'index.php?option=com_sellacious&task=productmedia.download&p=' . $field->code . '&file=%s&version=latest';
$group = $media->files_group ?: 'files';
?>
<div class="jff-productmedia-row" data-media-id="<?php echo (int) $media->id ?>">
	<div class="jff-productmedia-column jff-productmedia-group">
		<span class="pull-left"><?php echo $group ?></span>
		<a class="jff-productmedia-hotlink hasTooltip btn-copy-code pull-right" data-placement="left"
		   title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_HOTLINK_COPY') ?>"
		   data-text="<?php echo sprintf($linkM, $group) ?>">  <i class="fa fa-copy"></i></a>
		<span class="pull-right"> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_MEDIA_LABEL_LATEST') ?> &nbsp; </span>
	</div>
</div>
