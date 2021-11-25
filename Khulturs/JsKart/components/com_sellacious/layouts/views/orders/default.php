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

/** @var SellaciousViewOrders $this */
$app = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('ctech.bootstrap');

JHtml::_('behavior.formvalidator');
JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.orders.tile.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

$orders = $this->items;
?>
<div class="ctech-wrapper">
	<script>
		Joomla.submitbutton = function (task, form1) {
			var form = form1 || document.getElementById('adminForm');

			if (document.formvalidator.isValid(form)) {
				Joomla.submitform(task, form);
			} else {
				form1 && Joomla.removeMessages();
				alert('<?php echo JText::_('COM_SELLACIOUS_ORDER_FORM_VALIDATION') ?>');
			}
		};
	</script>
	<div class="order-tabs">
		<?php
		if ($orders):
			$orderSingle = reset($orders);
			echo JHtml::_('ctechBootstrap.startTabs', 'orders_tabs', array('vertical' => true, 'active' => 'tab_' . $orderSingle->order_number));
			foreach ($orders as $order)
			{
				$c_currency = $this->helper->currency->current('code_3');
				$paid       = $this->helper->order->isPaid($order->id);
				$paid_icon  = $paid ? 'paid fa-check ctech-text-success' : 'not-paid fa-times ctech-text-danger';

				$tabTitle = '<div class="order-tab-details">
                            <span class="order-number">' . $order->order_number . '</span> 	
                            <span class="order-total ctech-float-right"><span>' . $this->helper->currency->display($order->grand_total, $order->currency, $c_currency, true)
					. '</span>&nbsp; <i class="fa order-' . $paid_icon . '"> </i></span></div>';
				echo JHtml::_('ctechBootstrap.addTab', 'tab_' . $order->order_number, $tabTitle, 'orders_tabs');
				echo $this->loadTemplate('tile', $order);
				echo $this->loadTemplate('tile_modals', $order);
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
