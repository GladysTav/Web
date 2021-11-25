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

JHtml::_('script', 'media/com_sellacious/js/plugin/select2/select2.min.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'media/com_sellacious/js/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/util.tabstate.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			let $select = $('select');
			$select.css('width', '100%');
			$select.select2();
		});
	})(jQuery);

	Joomla.submitbutton = function (task) {
		let form = document.getElementById('adminForm');
		let task2 = task.split('.')[1] || '';
		if (task2 === 'setType' || task2 === 'cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}
	}
</script>
<div class="row">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" class="form-validate form-horizontal"
	      name="adminForm" id="adminForm">
		<!-- NEW WIDGET START -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="tabsedit-container-off">

				<div class="widget-body edittabs">
					<div id="myTabContent3" class="tab-content padding-10" style="margin-left: 0">
						<div class="tab-pane fade in active" id="tab-permissions">
							<fieldset>
								<?php
								$fields = $this->form->getFieldset('permissions');

								foreach ($fields as $field)
								{
									if ($field->hidden)
									{
										echo $field->input;
									}
									else
									{
										$oLbl  = $field->type == 'Note';
										$hLbl  = $field->label == '' || (isset($fieldset->width) && $fieldset->width == 12);
										$clazz = ($field->label && !$oLbl) ? 'input-row' : '';

										echo $field->renderField(array('hiddenLabel' => $hLbl, 'onlyLabel' => $oLbl, 'class' => $clazz));
									}
								}
								?>
							</fieldset>
						</div>
					</div>
					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</div>

			</div>
			<!-- end widget -->

		</article>
		<!-- WIDGET END -->
	</form>
</div>
