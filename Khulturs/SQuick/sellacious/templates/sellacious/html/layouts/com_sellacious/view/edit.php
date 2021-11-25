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

JHtml::_('jquery.framework');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'com_sellacious/plugin/select2/select2.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/view.field.js', array('relative' => true, 'version' => S_VERSION_CORE));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
JText::script('COM_SELLACIOUS_FIELD_FIELD_FILTERABLE_NOT_ALLOWED');

/** @var array $displayData */
?>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	// Skip already converted select2
	$('select').not('.select2-offscreen').select2();
});

Joomla.submitbutton = function (task) {
	var form = document.getElementById('adminForm');
	var task2 = task.split('.')[1] || '';
	if (task2 == 'setType' || task2 == 'cancel' || document.formvalidator.isValid(form)) {
		Joomla.submitform(task, form);
	} else {
		alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
	}
}
</script>
<div class="row editboxes">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" enctype="multipart/form-data">
		<?php
		/** @var  JForm  $form */
		$form      = $displayData['form'];
		$fieldsets = $form->getFieldsets();

		foreach ($fieldsets as $fs_key => $fieldset)
		{
			$visible = array();
			$fields  = $form->getFieldset($fieldset->name);

			// echo hidden input right away, and collect others for the box
			foreach ($fields as $field)
			{
				if ($field->hidden)
				{
					echo $field->input;
				}
				else
				{
					$visible[] = $field;
				}
			}

			if (count($visible))
			{
			    $col = isset($fieldset->width) ? $fieldset->width : '6';
				?>
				<article class="col-sm-12 col-md-12 col-lg-<?php echo $col ?>">
					<!-- Widget ID (each widget will need unique ID)-->
					<div class="jarviswidget" id="wid-id-<?php echo $fs_key ?>">
						<?php if ($fieldset->label): ?>
						<header><span class="widget-icon"><i class="fa fa-tasks"></i></span>
							<h2><?php echo JText::_($fieldset->label, true) ?></h2></header>
						<?php endif; ?>
						<!-- widget content -->
						<div class="widget-body edittabs">
							<fieldset>
								<?php
								foreach ($visible as $field)
								{
									$oLbl  = $field->type == 'Note';
									$hLbl  = $field->label == '' || (isset($fieldset->width) && $fieldset->width == 12);
									$clazz = ($field->label && !$oLbl) ? 'input-row' : '';

									echo $field->renderField(array('hiddenLabel' => $hLbl, 'onlyLabel' => $oLbl, 'class' => $clazz, 'lblSize' => 'lg'));
								}
								?>
							</fieldset>
						</div>
						<!-- end widget content -->
					</div>
					<!-- end widget -->
				</article>
				<?php
			}
		}
		?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
