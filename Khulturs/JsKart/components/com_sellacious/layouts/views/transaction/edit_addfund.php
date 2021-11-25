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
?>
<div class="ctech-wrapper transaction-add">
	<div class="ajax-overlay"></div>
	<form action="<?php echo JUri::getInstance()->toString(); ?>"
		  method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal"
		  enctype="multipart/form-data">
		<div class="addfund-heading">
			<h2><?php echo JText::_('COM_SELLACIOUS_TRANSACTION_ADD_FUND') ?>
				<a href="<?php echo $link; ?>" role="button"
				   class="ctech-mb-3 ctech-float-right ctech-text-primary"><i class="fa fa-backward"></i> <span><?php
						echo JText::_('COM_SELLACIOUS_TRANSACTION_GO_BACK_TO_TRANSACTIONS'); ?></span></a></h2>
		</div>

		<div class="addfund-form">
			<div class="addfund-form-content ">
				<?php foreach ($form->getFieldset() as $field): ?>
					<?php if (strtolower($field->type) == 'hidden'): ?><div class="ctech-col-form-input">
						<?php echo $field->input ?></div>
					<?php else: ?>
						<div class="ctech-form-group">
							<div class="ctech-col-form-label text-left "><?php echo $field->label ?></div>
							<div class="ctech-col-form-input"><?php echo $field->input ?></div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<?php echo JHtml::_('form.token'); ?>
	</form>

	<div id="payment-forms">
		<?php
		$options       = array('debug' => 0);
		$args          = new stdClass;
		$args->methods = $this->helper->paymentMethod->getMethods('addfund', true, $me->get('id'));
		$html          = JLayoutHelper::render('com_sellacious.payment.forms', $args, '', $options);

		echo $html;
		?>
	</div>
</div>
