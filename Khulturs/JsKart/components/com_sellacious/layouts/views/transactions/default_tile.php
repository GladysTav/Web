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

/** @var  SellaciousViewTransactions $this */
/** @var  stdClass $tplData */
$transaction = $tplData;

$c_currency = $this->helper->currency->current('code_3');
$paid       = $transaction->state == 1;
$dispatcher = $this->helper->core->loadPlugins();

$crdr = $transaction->crdr == 'cr' ? '(+)' : '(-)';
?>
<div class="transaction-basic-details">
	<div class="transaction-basic-head">
		<span><?php echo JHtml::_('date', $transaction->txn_date, 'D, F d, Y h:i A') ?></span>
		<i class="fa transaction-<?php echo $paid ? 'paid fa-check ctech-text-success' : 'not-paid fa-times ctech-text-danger' ?>"> </i>
		<span class="transaction-total ctech-float-right">
			<?php echo JText::sprintf('COM_SELLACIOUS_TRANSACTION_AMOUNT_LABEL', $crdr); ?>:
			<strong><?php echo $this->helper->currency->display($transaction->amount, $transaction->currency, $c_currency, true); ?></strong>
		</span>
	</div>
	<div class="transaction-basic-body">
		<div class="transaction-item">
			<div class="transaction-item-info">
				<div class="ctech-row">
					<div class="ctech-col-md-3"><?php echo JText::_("COM_SELLACIOUS_TRANSACTION_REASON"); ?></div>
					<div class="ctech-col-md-9"><?php echo $transaction->reason; ?></div>
				</div>

				<?php if ($transaction->order_id): ?>
					<div class="ctech-row">
						<div class="ctech-col-md-3"><?php echo JText::_("COM_SELLACIOUS_TRANSACTION_ORDER_ID"); ?></div>
						<div class="ctech-col-md-9"><?php echo $transaction->order_id; ?></div>
					</div>
				<?php endif; ?>

				<?php if ($transaction->notes): ?>
					<div class="ctech-row">
						<div class="ctech-col-md-3"><?php echo JText::_("COM_SELLACIOUS_TRANSACTION_NOTES"); ?></div>
						<div class="ctech-col-md-9"><?php echo $transaction->notes; ?></div>
					</div>
				<?php endif; ?>

				<?php if ($transaction->user_notes): ?>
					<div class="ctech-row">
						<div class="ctech-col-md-3"><?php echo JText::_("COM_SELLACIOUS_TRANSACTION_USER_NOTES"); ?></div>
						<div class="ctech-col-md-9"><?php echo $transaction->user_notes; ?></div>
					</div>
				<?php endif; ?>

				<?php if ($transaction->admin_notes): ?>
					<div class="ctech-row">
						<div class="ctech-col-md-3"><?php echo JText::_("COM_SELLACIOUS_TRANSACTION_ADMIN_NOTES"); ?></div>
						<div class="ctech-col-md-9"><?php echo $transaction->admin_notes; ?></div>
					</div>
				<?php endif; ?>
			</div>
			<div class="ctech-clearfix"></div>
			<div class="transaction-item-status">
				<?php echo JText::_("COM_SELLACIOUS_TRANSACTION_STATUS"); ?>
				<span class="status-item <?php echo $paid ? 'ctech-text-success' : 'ctech-text-danger' ?>">
					<?php echo JText::plural('COM_SELLACIOUS_TRANSACTION_HEADING_STATE_X', $transaction->state); ?>
				</span>
			</div>
		</div>
	</div>
</div>
