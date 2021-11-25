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

JHtml::_('bootstrap.tooltip');
JHtml::_('jquery.framework');
JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', false, false);

$token = JSession::getFormToken();
$uri   = new JUri('index.php?option=com_sellacious&task=users.csvTemplate&dl=1');
$uri->setVar($token, 1);

$headers = (array) $this->state->get('headers');
$columns = (array) $this->state->get('columns');
$require = (array) $this->state->get('required');
$alias   = (array) $this->state->get('alias');

$alias   = new Joomla\Registry\Registry($alias);
?>
<script>
	jQuery(document).ready(function ($) {
		var $selects = $('.select2-alias');
		var choices = <?php echo json_encode($headers); ?>;
		var data = $.map(choices, function (choice) {
			return {id: choice, text: choice};
		});
		$selects.select2({
			allowClear: true,
			data: function () {
				var used = [];
				$.each($selects, function (index, el) {
					var val = $(el).val();
					if (val != '') used.push(val);
				});
				var options = $.grep(data, function (choice) {
					return $.inArray(choice.id, used) == -1;
				});
				return {results: options};
			},
			initSelection : function (element, callback) {
				var val = $(element).val();
				if ($.inArray(val, choices) >= 0)
					callback({id: val, text: val});
			}
		});
	});
</script>

<div class="importer-block">
	<label><?php echo JText::_('COM_SELLACIOUS_IMPORT_CSV_FORMAT_CONDITION') ?></label>
	<br>
	<?php $uri->setVar('specs', '0'); ?>
	<a class="btn btn-info" href="<?php echo $uri->toString() ?>"><?php echo
		JText::_('COM_SELLACIOUS_IMPORT_CSV_SAMPLE_DOWNLOAD'); ?></a>
</div>

<div class="importer-block upload-bar">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=import') ?>"
		  method="post" class="form-horizontal" enctype="multipart/form-data">

		<div class="uploadform-content pull-left">
			<div class="jff-fileplus-wrapper">
				<div class="jff-fileplus-active center">
					<div class="bg-color-white upload-input w100p">
						<a class="btn btn-sm btn-primary jff-fileplus-add" style="float: none;"><i
						   class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_IMPORT_UPLOAD_CSV'); ?></a>
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

		<input type="hidden" name="source" value="Users"/>
		<input type="hidden" name="task" value="import.upload"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<div class="clearfix"></div>
</div>

<?php if ($this->state->get('source') == 'Users'): ?>
<div class="importer-block upload-bar">
	<form action="<?php echo JRoute::_('index.php?option=com_sellacious&view=import') ?>"
		  method="post" class="form-horizontal" enctype="multipart/form-data">

		<?php if ($this->state->get('active')): ?>
			<a class="btn btn-sm btn-warning btn-import active"><i class="fa fa-spinner"></i>&nbsp;
				<?php echo JText::_('COM_SELLACIOUS_IMPORT_BUTTON_VIEW_STATUS'); ?></a>
			<div class="queue-note note txt-color-red"><br/><?php echo JText::_('COM_SELLACIOUS_IMPORT_STATE_RUNNING'); ?></div>
		<?php else: ?>
			<div class="queue-note note txt-color-red"><br/><?php echo JText::_('COM_SELLACIOUS_IMPORT_STATE_PENDING'); ?></div>
			<div class="clearfix"></div>
			<br>
			<table class="import-options table-column-map pull-left">
				<?php foreach ($columns as $column): ?>
					<?php $required = in_array($column, $require); ?>
					<tr class="column-map">
						<td><label for="field_<?php echo $column ?>"><?php echo $column ?> <?php echo $required ? '*' : ''; ?></label></td>
						<td>
							<?php $value = $alias->get($column, $column); ?>
							<input type="hidden" name="alias_<?php echo $column ?>" id="field_<?php echo $column ?>"
								   class="select2-alias" value="<?php echo $value ?>" <?php echo $required ? 'required' : ''; ?>/>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<div class="clearfix"></div>
			<br>
			<a class="btn btn-sm btn-success btn-import"><i	class="fa fa-spinner"></i>&nbsp;
				<?php echo JText::_('COM_SELLACIOUS_IMPORT_BUTTON_START_IMPORT'); ?></a>
		<?php endif; ?>

		<div class="import-log hidden"></div>

		<input type="hidden" name="source" value="Users"/>
		<input type="hidden" name="task" value="import.upload"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<div class="clearfix"></div>
</div>
<?php endif; ?>
