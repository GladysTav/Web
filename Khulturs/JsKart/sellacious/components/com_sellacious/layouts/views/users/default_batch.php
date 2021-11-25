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

$fields = $this->filterForm->getGroup('batch');
?>
<br/>
<script>
	jQuery(document).ready(function ($) {
		$('#batch_action').find('input[type="radio"]').change(function () {
			$('#batch_filter').closest('.input-row').toggleClass('hidden', $(this).val() == -1);
		}).triggerHandler('change');
	});
</script>
<div class="jarviswidget" id="wid-id-1000">
	<header>
		<span class="widget-icon"><i class="fa fa-tasks"></i></span>
		<h2><?php echo JText::_('COM_SELLACIOUS_USERS_BATCH_OPTIONS') ?></h2>
	</header>
	<div>
		<!-- widget content -->
		<div class="widget-body">
			<fieldset>

				<div  class="row clearfix">
					<br/><p class="form-label col-sm-8 col-md-8 col-lg-6"><?php
						echo JText::_('COM_SELLACIOUS_USERS_BATCH_TIP'); ?></p><br/>
				</div>

				<div class="clearfix"></div><br/>

				<?php foreach ($fields as $field) : ?>
					<?php if ($field->hidden) : ?>
						<?php echo $field->input; ?>
					<?php else : ?>
					<div  class="row input-row">
						<div class="form-label col-sm-3 col-md-3 col-lg-2">
							<?php echo $field->label; ?>
						</div>
						<div class="controls col-sm-9 col-md-9 col-lg-6">
							<?php echo $field->input; ?>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>

				<div class="clearfix"></div><br/>

				<div class="btn-group">
					<button type="submit" class="btn btn-primary btn-validate" onclick="Joomla.submitform('user.batch', this.form, true);">
						<?php echo JText::_('COM_SELLACIOUS_BTN_BATCH_PROCESS'); ?>
					</button>
				</div>

			</fieldset>
		</div>
		<!-- end widget content -->
	</div>
</div>
