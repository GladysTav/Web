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

/** @var  SellaciousViewOrder  $this */
$order      = new Registry($this->item);
$items      = $order->get('items');
$o_currency = $order->get('currency');
$c_currency = $this->helper->currency->current('code_3');

$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));
$app = JFactory::getApplication();

if ($app->input->get('format') == 'pdf')
{
	?>
	<style>
		<?php echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/component.css'); ?>
		<?php echo file_get_contents(JPATH_SITE . '/media/com_sellacious/css/view.order.invoice.css'); ?>
	</style>
	<?php
}
else
{
	JHtml::_('stylesheet', 'com_sellacious/component.css', array('version' => S_VERSION_CORE, 'relative' => true));
	JHtml::_('stylesheet', 'com_sellacious/view.order.invoice.css', array('version' => S_VERSION_CORE, 'relative' => true));
}
?>
<?php if ($app->input->get('tmpl') == 'component'): ?>
	<script>
		jQuery(function($) {
			$(document).ready(function () {
				window.print();
			});
		});
	</script>
<?php else: ?>
	<div id="receipt-head" class="text-right">
		<?php $print = JRoute::_('index.php?option=com_sellacious&view=order&layout=invoice&tmpl=component&id=' . $order->get('id')); ?>
		<a class="btn btn-sm btn-primary" target="_blank" href="<?php echo $print ?>"><i class="fa fa-print"></i> <?php echo JText::_('COM_SELLACIOUS_ORDER_PRINT'); ?></a>
	</div>
<?php endif; ?>
<?php
	$html 		= '';
	$dispatcher = $this->helper->core->loadPlugins();
	$dispatcher->trigger('onParseViewTemplate', array('com_sellacious.backoffice.order.invoice', $order, &$html));
?>
<?php echo $html; ?>
