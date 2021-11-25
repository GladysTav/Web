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

/** @var SellaciousViewProduct $this */

if ($this->form)
{
	JHtml::_('jquery.framework');
	JHtml::_('behavior.formvalidator');
	JHtml::_('bootstrap.tooltip', '.hasTooltip');

	JHtml::_('ctech.select2');
	JHtml::_('stylesheet', 'com_sellacious/fe.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
	JHtml::_('script', 'com_sellacious/fe.view.product.coq.js', array('version' => S_VERSION_CORE, 'relative' => true));

	JText::script('COM_SELLACIOUS_VALIDATION_FORM_FAILED');
	JText::script('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_SUBMIT');
	JText::script('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_SUBMIT_PROCESSING');

	$form = $this->form;
	?>
	<style>
		.form-horizontal .control-label.left {
			text-align: left;
		}
		.margin-center {
			margin-right: auto;
			margin-left: auto;
			float: left;
		}
		.coq_form_wrapper {
			margin-bottom: 10px;
		}
	</style>
	<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>" method="post"
		  id="coqForm" name="coqForm" class="ctech-form-horizontal">
		<div class="w100p">
			<div class="margin-top-10">
				<div class="coq_form_wrapper">
					<?php echo JLayoutHelper::render('sellacious.product.forms.checkout_questions', array('form' => $form)); ?>
				</div>
				<div class="ctech-clearfix"></div>
				<button type="button" class="ctech-btn ctech-btn-small ctech-btn-primary ctech-pull-right btn-coq-submit">
					<?php echo JText::_('COM_SELLACIOUS_PRODUCT_CHECKOUT_QUESTIONS_FORM_SUBMIT'); ?>
				</button>
			</div>
		</div>

		<input type="hidden" name="p" id="product_code" value="<?php echo $this->state->get('product.code') ?>" />

		<input type="hidden" name="option" value="com_sellacious" />
		<input type="hidden" name="view" value="product" />
		<input type="hidden" name="layout" value="coq" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<?php
}
