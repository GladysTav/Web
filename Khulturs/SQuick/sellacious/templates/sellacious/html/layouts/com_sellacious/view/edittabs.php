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

/** @var JForm $form */
/** @var array $displayData */
$form      = $displayData['form'];
$multipart = isset($displayData['multipart']) && $displayData['multipart'] === true;
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			var $select = $('select');
			$select.css('width', '100%');
			$select.select2();
		});
	})(jQuery);

	Joomla.submitbutton = function (task) {
		var form = document.getElementById('adminForm');
		var task2 = task.split('.')[1] || '';
		if (task2 === 'setType' || task2 === 'cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}
	}
</script>
<div class="row">
	<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post" class="form-validate form-horizontal"
		  name="adminForm" id="adminForm" <?php echo $multipart ? ' enctype="multipart/form-data" ' : '' ?>>
		<!-- NEW WIDGET START -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="tabsedit-container-off">

				<!-- widget div-->
				<div>

					<!-- widget content -->
					<div class="widget-body edittabs">
						<?php
						$fieldsets = $form->getFieldsets();
						$visible   = array();

						// Find visible tabs
						foreach ($fieldsets as $fs_key => $fieldset)
						{
							$fields = $form->getFieldset($fieldset->name);

							$visible[$fs_key] = 0;

							// Skip if fieldset is empty.
							if (count($fields) == 0)
							{
								continue;
							}

							foreach ($fields as $field)
							{
								if (!$field->hidden)
								{
									$visible[$fs_key]++;
								}
							}
						}

						// Add links for visible tabs
						?>
						<div class="tab-menu-head">
							<a class="tabbar-toggler" href="javascript:void(0);">
								<span class="active-tab"><?php echo JText::_('COM_SELLACIOUS_TAB_BAR_LABEL'); ?></span>
								<div class="hamburger">
									<span></span>
									<span></span>
									<span></span>
								</div>
							</a>
						</div>
						<ul id="myTab3" class="nav nav-tabs tabs-pull-left bordered">
							<?php
							$counter = 0;

							foreach ($fieldsets as $fs_key => $fieldset)
							{
								if ($visible[$fs_key])
								{
									$class = ($counter ? '' : ' active') . ((isset($fieldset->align) && $fieldset->align == 'right') ? 'pull-right' : '');
									?>
									<li class="<?php echo $class ?>">
										<a href="#tab-<?php echo $fs_key; ?>" data-toggle="tab">
											<?php echo JText::_($fieldset->label, true) ?>
										</a>
									</li>
									<?php
									$counter++;
								}
							}
							?>
						</ul>
						<?php // Add content for visible tabs ?>
						<div id="myTabContent3" class="tab-content padding-10"><?php

							$counter = 0;

							foreach ($fieldsets as $fs_key => $fieldset)
							{
								if ($visible[$fs_key])
								{
									$fields = $form->getFieldset($fieldset->name); ?>
									<div class="tab-pane fade<?php echo ($counter++) ? '' : ' in active' ?>" id="tab-<?php echo $fs_key ?>">
										<fieldset>
											<?php
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
									<?php
								}
							}

							?></div><?php

						// Add (remaining) content for invisible tabs
						foreach ($fieldsets as $fs_key => $fieldset)
						{
							if (!$visible[$fs_key])
							{
								$fields = $form->getFieldset($fieldset->name);

								foreach ($fields as $field)
								{
									echo $field->input;
								}
							}
						}

						?>
						<input type="hidden" name="task" value="" />
						<?php echo JHtml::_('form.token'); ?>
					</div>
					<!-- end widget content -->

				</div>
				<!-- end widget div -->

			</div>
			<!-- end widget -->

		</article>
		<!-- WIDGET END -->
	</form>
</div>
