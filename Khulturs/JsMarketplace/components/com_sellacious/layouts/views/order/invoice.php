<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Order\Invoice;

/** @var SellaciousViewOrder $this */
$order              = new Registry($this->item);
$items              = $order->get('items');
$c_currency         = $this->helper->currency->current('code_3');
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$app                = JFactory::getApplication();
$dispatcher         = $this->helper->core->loadPlugins();

if ($app->input->get('format') == 'pdf')
{
	?>
	<style>
		<?php
		echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/fe.component.css');
		echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/fe.view.order.invoice.css');
		?>
	</style>
	<?php
}
else
{
	JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
	JHtml::_('stylesheet', 'com_sellacious/fe.view.order.invoice.css', null, true);
}

$seller_separate_invoice = $this->helper->config->get('seller_separate_invoice', 0);

if ($seller_separate_invoice == 1):
	$sellers = $this->helper->order->getSellers($order->get('id'));

	foreach ($sellers as $seller)
	{
		$html         = '';
		$orderInvoice = new Invoice($order->get('id'), $seller->seller_uid);
		$invoice      = $orderInvoice->getInvoiceData();

		$dispatcher->trigger('onParseViewTemplate', array('com_sellacious.order.invoice', $invoice, &$html, array('seller_uid' => $seller->seller_uid)));
		?>
		<div class="invoice-page">
			<?php echo $html; ?>
			<div class="clearfix"></div>
		</div>
		<hr>
		<div style="page-break-before: always;"></div>
		<?php
	}
else:
	$html = '';
	$dispatcher->trigger('onParseViewTemplate', array('com_sellacious.order.invoice', $order, &$html));
	?>
	<div class="invoice-page">
		<?php echo $html; ?>
		<div class="clearfix"></div>
	</div>
<?php endif; ?>
