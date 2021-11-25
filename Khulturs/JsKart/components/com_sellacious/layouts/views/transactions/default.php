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

/** @var SellaciousViewTransactions $this */
$app = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('behavior.formvalidator');
JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.transactions.tile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

$transactions = $this->items;
$addFundLink  = JRoute::_('index.php?option=com_sellacious&task=transaction.add');
$withdrawLink = JRoute::_('index.php?option=com_sellacious&task=transaction.withdraw');
$balance      = $this->getWalletBalance();

$showAddFund  = $this->helper->config->get('show_transaction_add_fund', 1);
$showWithdraw = $this->helper->config->get('show_transaction_place_withdrawal', 1);
?>
<div class="ctech-wrapper">
	<div class="transaction-heading">
		<?php if ($showWithdraw): ?>
			<a href="<?php echo $withdrawLink; ?>" role="button"
			   class="ctech-mb-3 btn-withdraw-fund ctech-float-right ctech-text-primary"><i class="fa fa-minus"></i> <span
						class="withdraw-fund-text"><?php echo JText::_('COM_SELLACIOUS_TRANSACTIONS_WITHDRAW_FUND'); ?></span></a>
		<?php endif; ?>
		<?php if ($showAddFund): ?>
			<a href="<?php echo $addFundLink; ?>" role="button"
			   class="ctech-mb-3 btn-add-fund ctech-float-right ctech-text-primary"><i class="fa fa-plus"></i> <span
						class="add-fund-text"><?php echo JText::_('COM_SELLACIOUS_TRANSACTIONS_ADD_FUND'); ?></span></a>
		<?php endif; ?>
		<h6 class="ctech-float-left">
			<?php echo JText::_('COM_SELLACIOUS_TRANSACTIONS_EWALLET_BALANCE'); ?>
			<strong class="ctech-text-primary"><?php echo $balance ? $balance[0]->display : 0; ?></strong>
		</h6>
	</div>
	<div class="ctech-clearfix"></div>
	<div class="transaction-tabs">
		<?php
		if ($transactions):
			$transactionSingle = reset($transactions);
			echo JHtml::_('ctechBootstrap.startTabs', 'transactions_tabs', array('vertical' => true, 'active' => 'tab_' . $transactionSingle->txn_number));

			foreach ($transactions as $transaction)
			{
				$c_currency = $this->helper->currency->current('code_3');
				$paid       = $transaction->state == 1;
				$paid_icon  = $paid ? 'paid fa-check ctech-text-success' : 'not-paid fa-times ctech-text-danger';
				$crdr       = $transaction->crdr == 'cr' ? '(+)' : '(-)';

				$tabTitle = '<div class="transaction-tab-details">
							<span class="transaction-number">' . $transaction->txn_number . '</span>
							<span class="transaction-total"><span>' . $this->helper->currency->display($transaction->amount, $transaction->currency, $c_currency, true)
							. ' '  . $crdr . '</span>&nbsp; <i class="fa transaction-' . $paid_icon . '"> </i></span></div>';
				echo JHtml::_('ctechBootstrap.addTab', 'tab_' . $transaction->txn_number, $tabTitle, 'transactions_tabs');
				echo $this->loadTemplate('tile', $transaction);
				echo JHtml::_('ctechBootstrap.endTab');
			}

			echo JHtml::_('ctechBootstrap.endTabs');
		endif;
		?>
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
		  method="post" name="adminForm" id="adminForm">
		<table class="w100p">
			<tr>
				<td class="text-center">
					<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

		<?php
		if ($tmpl = $app->input->get('tmpl'))
		{
			?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
		}

		if ($layout = $app->input->get('layout'))
		{
			?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
		}

		echo JHtml::_('form.token');
		?>
	</form>
	<div class="ctech-clearfix"></div>
</div>
