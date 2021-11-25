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

$data    = array(
	'name'  => $this->getName(),
	'state' => $this->state,
	'item'  => $this->item,
	'form'  => $this->form,
);
$options = array(
	'client' => 2,
	'debug'  => 0,
);
?>
<div style="padding: 6px 15px 6px 6px;">

	<div class="box-toolbar">

		<div class="pull-left">
			<h1 class="page-title">
				<span class="fa fa-lg fa-file-zip-o"></span>
				<?php echo JText::_('COM_SELLACIOUS_TITLE_PRODUCTMEDIA') ?>
			</h1>
		</div>

		<div class="pull-right">
			<div class="btn-toolbar" role="toolbar" aria-label="Toolbar" id="toolbar">
				<div class="btn-wrapper btn-group" id="toolbar-save">
					<button type="button" class="button-apply btn btn-success btn-small"
					        onclick="Joomla.submitbutton('productmedia.save');">
						<i class="fa fa-save"></i>
						<span class="hidden-xs"> <?php echo JText::_('JTOOLBAR_APPLY') ?></span>
					</button>
				</div>
				<div class="btn-wrapper btn-group" id="toolbar-cancel">
					<button type="button" class="button-cancel btn btn-default btn-small"
					        onclick="window.parent && window.parent.jQuery('.iframe-drawer-close').click();">
						<i class="icon-cancel"></i>
						<span class="hidden-xs"> <?php echo JText::_('JTOOLBAR_CANCEL') ?></span>
					</button>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>

	</div>

	<div class="clearfix"></div>
	<br>

	<?php echo JLayoutHelper::render('com_sellacious.view.edit', $data, '', $options); ?>

</div>
<style>
	#system-message-container {
		padding: 10px 17px 0 10px;
	}
</style>
<script>
	(() => {
		if (window.parent !== window) {
			<?php if ($this->app->input->get('layout') === 'close'): ?>
			const pm = window.parent.jQuery('.jff-productmedia-wrapper').data('productmedia-instance');
			if (typeof pm === 'object' && typeof pm.refresh === 'function') {
				pm.refresh().done(() => window.parent.jQuery('.iframe-drawer-close').click());
			}
			<?php endif; ?>
		} else {
			<?php
			$id  = $this->state->get('productmedia.product_id');
			$url = 'index.php?option=com_sellacious&view=' . ($id ? 'product&layout=edit&id=' . $id : 'products');
			?>
			window.parent.location.href = "<?php echo $url ?>";
		}
		document.getElementById('adminForm').onsubmit = () => Joomla.loadingLayer('show');
	})();

	jQuery($ => {
		$('#jform_media_type').change(function () {
			const val = $(this).find('option').filter(':selected').val();
			$('#jform_media,#jform_media_wrapper').closest('div.row').toggleClass('hidden', val === 'link');
			$('#jform_media_url').closest('div.row').toggleClass('hidden', val === 'upload');
		}).triggerHandler('change');
		$('#jform_sample_type').change(function () {
			const val = $(this).find('option').filter(':selected').val();
			$('#jform_sample,#jform_sample_wrapper').closest('div.row').toggleClass('hidden', val === 'link');
			$('#jform_sample_url').closest('div.row').toggleClass('hidden', val === 'upload');
		}).triggerHandler('change');
	});
</script>
