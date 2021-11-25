<?php
/**
 * @version     1.5.2
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;
?>
<div class="importer-block">
	<label><?php echo JText::_('COM_SELLACIOUS_IMPORT_IMAGES_CONDITION_LABEL') ?></label>
	<br>
	<br>
	<?php echo JText::_('COM_SELLACIOUS_IMPORT_IMAGES_CONDITION_MESSAGE'); ?>
</div>

<div class="importer-block upload-bar">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=import') ?>" method="post" enctype="multipart/form-data">

		<p class="red"><?php echo JText::_('COM_SELLACIOUS_IMPORT_IMAGE_UPLOAD'); ?></p>

		<div class="uploadform-content pull-left">
			<div class="jff-fileplus-wrapper">
				<div class="jff-fileplus-active center">
					<div class="bg-color-white upload-input w100p">
						<a class="btn btn-sm btn-primary jff-fileplus-add" style="float: none;"><i
							class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_MSG_UPLOAD_MORE'); ?></a>
						<input type="file" name="import_file" class="hidden"/>
					</div>
					<div class="upload-process hidden">
						<i class="upload-progress"></i>
						<?php echo JText::_('COM_SELLACIOUS_IMPORT_WAIT'); ?>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>

		<input type="hidden" name="source" value="Images"/>
		<input type="hidden" name="task" value="import.upload"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<div class="clearfix"></div>
</div>

<div class="importer-block upload-bar">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=import') ?>" method="post" class="form-horizontal" enctype="multipart/form-data">

		<table class="form-controls">
			<tr>
				<td><label for="jform_key"><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_FILES_NAMED_BY'); ?></label></td>
				<td>
					<div class="btn-group" data-toggle="buttons" id="jform_key">
						<label class="btn btn-default active"><input type="radio" name="key" value="sku" checked><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_BUTTON_PRODUCT_SKU'); ?></label>
						<label class="btn btn-default"><input type="radio" name="key" value="alias" ><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_BUTTON_PRODUCT_ALIAS'); ?></label>
						<label class="btn btn-default"><input type="radio" name="key" value="product_id"><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_BUTTON_PRODUCT_ID'); ?></label>
					</div>
				</td>
			</tr>
			<tr>
				<td><label for="jform_context"><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_IMPORT_AS'); ?></label></td>
				<td>
					<div class="btn-group" data-toggle="buttons" id="jform_context">
						<label class="btn btn-default active"><input type="radio" name="context" value="products.images" checked><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_BUTTON_PRODUCT_IMAGES'); ?></label>
						<label class="btn btn-default"><input type="radio" name="context" value="products.attachments"><?php echo JText::_('COM_SELLACIOUS_IMPORT_LABEL_BUTTON_PRODUCT_ATTACHMENTS'); ?></label>
					</div>
				</td>
			</tr>
		</table>

		<div class="clearfix"></div>
		<br>
		<a class="btn btn-sm btn-success btn-import-images"><i class="fa fa-image"></i>&nbsp;
			<?php echo JText::_('COM_SELLACIOUS_IMPORT_BUTTON_START_IMPORT'); ?></a>

		<div class="clearfix"></div>
		<div class="import-log hidden"></div>

		<?php echo JHtml::_('form.token'); ?>
	</form>
	<div class="clearfix"></div>
</div>

