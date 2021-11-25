<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/plugin/bootstrap-tags/bootstrap-tagsinput.min.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('script', 'com_sellacious/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.variant.css', array('version' => S_VERSION_CORE, 'relative' => true));

$variantId = $this->state->get('variant.id', 0);
?>
<div class="variant-edit-form">
	<div class="box-toolbar">

		<div class="pull-left">
			<h1 class="page-title">
				<span class="fa fa-lg fa-file"></span>
				<?php echo JText::_('COM_SELLACIOUS_TITLE_VARIANT') ?>
			</h1>
		</div>

		<div class="pull-right">
			<div class="btn-toolbar" role="toolbar" aria-label="Toolbar" id="toolbar">
				<div class="btn-wrapper btn-group" id="toolbar-apply">
					<button type="button" class="button-apply btn btn-success btn-small"
					        onclick="Joomla.submitbutton('variant.apply');">
						<i class="fa fa-save"></i>
						<span class="hidden-xs"> <?php echo JText::_('JAPPLY') ?></span>
					</button>
				</div>
				<div class="btn-wrapper btn-group" id="toolbar-save">
					<button type="button" class="button-save btn btn-success btn-small"
					        onclick="Joomla.submitbutton('variant.save');">
						<i class="fa fa-save"></i>
						<span class="hidden-xs"> <?php echo JText::_('JTOOLBAR_SAVE') ?></span>
					</button>
				</div>
				<div class="btn-wrapper btn-group" id="toolbar-save">
					<button type="button" class="button-copy btn btn-default btn-small"
					        onclick="Joomla.submitbutton('variant.save2copy');">
						<i class="fa fa-copy"></i>
						<span class="hidden-xs"> <?php echo JText::_('JTOOLBAR_SAVE_AS_COPY') ?></span>
					</button>
				</div>
				<div class="btn-wrapper btn-group" id="toolbar-cancel">
					<button type="button" class="button-cancel btn btn-default btn-small"
					        onclick="window.parent && window.parent.jQuery('.iframe-drawer-close').click();">
						<i class="icon-cancel"></i>
						<span class="hidden-xs"> <?php echo JText::_('COM_SELLACIOUS_VARIANT_CLOSE_DISCARD') ?></span>
					</button>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>

	<div class="clearfix"></div>
	<br>

	<?php echo $this->loadTemplate('edit'); ?>
</div>
<script>
	jQuery($ => {
		if (window.parent !== window) {
			<?php if ($this->app->input->get('layout') === 'close'): ?>

			var tabVariant = window.parent.document.tabVariant;
			tabVariant.getVariant(<?php echo $variantId ?>, function (data) {
				tabVariant.addRow(data);
			});

			window.parent.jQuery('.iframe-drawer-close').click();
				<?php elseif ($this->app->input->get('layout') === 'apply'): ?>

			var tabVariant = window.parent.document.tabVariant;
			tabVariant.getVariant(<?php echo $variantId ?>, function (data) {
				tabVariant.addRow(data);
			});
			<?php endif; ?>
		} else {
			<?php
			$id  = $this->state->get('variant.productId');
			$url = 'index.php?option=com_sellacious&view=' . ($id ? 'product&layout=edit&id=' . $id : 'products');
			?>
			window.parent.location.href = "<?php echo $url ?>";
		}
		document.getElementById('adminForm').onsubmit = () => Joomla.loadingLayer('show');
	});
</script>
