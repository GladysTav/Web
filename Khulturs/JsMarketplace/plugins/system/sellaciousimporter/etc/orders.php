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
JHtml::_('stylesheet', 'com_sellacious/view.import.products.css', null, true);

$token = JSession::getFormToken();
$uri   = new JUri('index.php?option=com_sellacious&task=orders.csvTemplate&dl=1');
$uri->setVar($token, 1);

$headers = (array) $this->state->get('headers');
$columns = (array) $this->state->get('columns');
$require = (array) $this->state->get('required');
$alias   = (array) $this->state->get('alias');

$alias   = new Joomla\Registry\Registry($alias);
?>
<div id="import-orders">

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

			<p class="red"><?php echo JText::_('COM_SELLACIOUS_IMPORT_ORDERS_CSV_NOTE'); ?></p>

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

			<input type="hidden" name="source" value="Orders"/>
			<input type="hidden" name="task" value="import.upload"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
		<div class="clearfix"></div>
	</div>

	<?php if ($this->state->get('source') == 'Orders'): ?>

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
				<?php $countC = count($columns); ?>
				<?php $countH = count($headers); ?>
				<table id="sortable-area-orders" class="import-options table-column-map table-bordered table-drag">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_SELLACIOUS_IMPORT_HEADING_COLUMNS_IMPORTABLE')?></th>
							<th style="min-width: 200px;"><?php echo JText::_('COM_SELLACIOUS_IMPORT_HEADING_COLUMNS_UPLOADED_MAPPING')?></th>
							<th><?php echo JText::_('COM_SELLACIOUS_IMPORT_HEADING_COLUMNS_UPLOADED')?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					for ($row = 0; $row < $countC; $row++)
					{
						$column   = $columns[$row];
						$required = in_array($column, $require);
						?>
						<tr class="column-map">
							<td><?php echo $this->escape($column) ?><span class="red"><?php echo $required ? '*' : ''; ?></span></td>
							<td>
								<ul class="sortable-group alias-drop" data-column="<?php echo $this->escape($column); ?>">
									<?php if (in_array($column, $headers)): ?>
										<li class="sortable-item" data-alias="<?php
											echo $this->escape($column); ?>"><?php echo $this->escape($column); ?></li>
									<?php endif; ?>
								</ul>
							</td>
							<?php if ($row == 0): ?>
							<td rowspan="<?php echo max($countH, $countC) ?>" class="v-top no-hover headers-cell">
								<div class="headers-container">
									<ul class="sortable-group headerList">
										<?php for ($index = 0; $index < $countH; $index++): ?>
											<?php $header = $headers[$index]; ?>
											<?php if (!in_array($header, $columns)): ?>
												<li class="sortable-item" data-alias="<?php
													echo $this->escape($header); ?>"><?php echo $this->escape($header); ?></li>
											<?php endif; ?>
										<?php endfor; ?>
									</ul>
								</div>
							</td>
							<?php endif; ?>
						</tr>
						<?php
					}

					if ($countC < $countH)
					{
						?><tr><td colspan="2" rowspan="<?php echo $countH - $countC ?>">&nbsp;</td></tr><?php
					}
					?>
					</tbody>
				</table>
				<br>

				<div class="clearfix"></div>
				<br>
				<a class="btn btn-sm btn-success btn-import"><i	class="fa fa-spinner"></i>&nbsp;
					<?php echo JText::_('COM_SELLACIOUS_IMPORT_BUTTON_START_IMPORT'); ?></a>
			<?php endif; ?>

			<div class="import-log hidden"></div>

			<input type="hidden" name="source" value="Orders"/>
			<input type="hidden" name="task" value="import.upload"/>
			<?php echo JHtml::_('form.token'); ?>
		</form>
		<div class="clearfix"></div>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function ($) {
			var $sortable = $('#sortable-area-orders');
			$sortable.find('.sortable-group').sortable({
				connectWith: '#sortable-area-orders .sortable-group',
				placeholder: 'placeholder'
			});
			$sortable.find('.sortable-group').disableSelection();
			$sortable.find('ul.alias-drop').on('sortreceive', function (event, ui) {
				$(this).children().not(ui.item).appendTo('.headerList');
			});
			$sortable.find('.headers-container').draggable({axis: 'y', containment: '.headers-cell'});
			$('select.hasSelect2').select2();
		});
	</script>

	<?php endif; ?>

</div>
