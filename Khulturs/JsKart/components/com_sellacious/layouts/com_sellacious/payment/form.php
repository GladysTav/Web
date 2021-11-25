<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass  $displayData */
/** @var  JForm     $form */
$method    = $displayData;
$form      = $method->form;
$fieldsets = $form->getFieldsets();
$helper    = SellaciousHelper::getInstance();
$image     = $helper->media->getImage('paymentmethod.logo', $method->id, false);
?>
<div class="payment-method">
	<div class="ctech-wrapper custom-attributes-container">
		<?php if ($image): ?>
			<p><img class="payment-method-logo pull-left" src="<?php echo $image ?>" alt="<?php echo htmlspecialchars($method->title) ?>"/></p>
		<?php endif; ?>

		<?php
		$fee_text   = array();
		$g_currency = $helper->currency->getGlobal('code_3');

		if (abs($method->percent_fee) >= 0.0001)
		{
			$fee_text[] = sprintf('%s%%', rtrim(number_format($method->percent_fee, 4), '.0'));
		}

		if (abs($method->flat_fee) >= 0.01)
		{
			$fee_text[] = $helper->currency->display($method->flat_fee, $g_currency, '', true);
		}
		?>

		<?php if ($fee_text): ?>
			<div class="payment-fee pull-right"><span class="payment-label"><?php
				echo JText::_('COM_SELLACIOUS_ORDER_PAYMENT_FEE_AMOUNT_PREFIX_LABEL'); ?></span><?php echo implode(' + ', $fee_text); ?></div>
		<?php endif; ?>

		<div class="clearfix"></div>

		<?php if (isset($method->credit_limit)): ?>
			<?php $cr_limit = $helper->currency->display($method->credit_limit, $g_currency, '', true); ?>
			<div class="payment-fee pull-right"><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_CREDIT_LIMIT_AMOUNT_PREFIX_LABEL', $cr_limit); ?></div>
		<?php endif; ?>

		<div class="clearfix"></div>

		<p><?php echo nl2br($method->description) ?></p>

		<form action="<?php echo JRoute::_('index.php') ?>" method="post" class="form-validate form-horizontal">
			<?php
			foreach ($fieldsets as $fs_key => $fieldset)
			{
				if ($fieldset->label)
				{
					echo '<h4>' . JText::_($fieldset->label, true) . '</h4>';
				}

				if ($fieldset->description)
				{
					echo '<p>' . JText::_($fieldset->description, true) . '</p>';
				}
				?>
				<div class="clearfix"></div>

				<div>
					<div>
					<?php
					$fields = $form->getFieldset($fieldset->name);

					foreach ($fields as $field)
					{
						if ($field->hidden)
						{
							echo $field->input;
						}
						elseif ($field->label)
						{
							?>
							<div class="ctech-form-group">
								<div class="ctech-col-form-label"><?php echo $field->label; ?></div>
								<div class="ctech-col-form-input"><?php echo $field->input; ?></div>
								<div class="ctech-clearfix"></div>
							</div>
							<?php
						}
						else
						{
							?>
						<div class="ctech-form-group">
							<div class="ctech-col-form-input"><?php echo $field->input; ?></div>
							<div class="ctech-clearfix"></div>
						</div>
							<?php
						}
					}
					?>
					</div>
				</div>
				<?php
			}
			?>
			<div class="payment-table">
				<div>
					<div colspan="2" class="text-right"><button type="button" class="ctech-btn ctech-btn-primary btn-pay-now"><?php
							echo JText::_('COM_SELLACIOUS_CART_PAYMENT_BUTTON_LABEL'); ?></button></div>
				</div>
			</div>
		</form>
	</div>
</div>
