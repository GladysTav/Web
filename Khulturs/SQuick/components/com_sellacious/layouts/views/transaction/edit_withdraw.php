<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

$me   = JFactory::getUser();
$form = $this->getForm();
$link = JRoute::_('index.php?option=com_sellacious&view=transactions', false);

$fieldsets = $form->getFieldsets();
?>
<div class="ctech-wrapper">
	<form action="<?php echo JUri::getInstance()->toString(); ?>"
		  method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal"
		  enctype="multipart/form-data">
		<div class="addfund-heading">
			<h2><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_WITHDRAW_FUND') ?>
				<a href="<?php echo $link; ?>" role="button"
				   class="ctech-mb-3 ctech-float-right ctech-text-primary"><i class="fa fa-backward"></i> <span
					><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_GO_BACK_TO_TRANSACTIONS'); ?></span></a></h2>
		</div>
		<div class="ctech-clearfix"></div>

		<div class="transaction-tabs withdraw-fund">
			<div class="ctech-wrapper">
				<div class="ctech-row">
					<div class="ctech-col-sm-12">
						<div class="addfund-form">
							<div class="addfund-form-content">
								<?php
								$count = 0;

								foreach ($fieldsets as $fieldset)
								{
									$fields = $this->form->getFieldset($fieldset->name);
									?>
									<div class="ctech-tab-pane ctech-fade <?php echo $count == 0 ? 'ctech-show ctech-active' : ''; ?>"
										 id="tab_<?php echo $fieldset->name; ?>">
										<?php
										foreach ($fields as $field):
											/** @var  \JFormField $field */
											if ($field->hidden):
												echo $field->input;
											else:
												$w = $field->getAttribute('fullwidth') == 'true';
												?>
												<div class="ctech-form-group">
													<div class="ctech-col-form-label text-left"><?php echo $field->label ?></div>
													<div class="ctech-col-form-input"><?php echo $field->input ?></div>
												</div>
											<?php
											endif;
										endforeach;
										?>
									</div>
									<?php
									$count++;
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="ctech-clearfix"></div>

		<button type="submit" class="ctech-btn ctech-btn-primary ctech-btn-sm ctech-float-right"><i
					class="fa fa-save"></i> <?php
			echo strtoupper(JText::_('COM_SELLACIOUS_SAVE')); ?></button>

		<input type="hidden" name="task" value="transaction.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
