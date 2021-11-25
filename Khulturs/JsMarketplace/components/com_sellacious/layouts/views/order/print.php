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

/** @var SellaciousViewOrder $this */
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order.print.css', null, true);

$order = new Registry($this->item);
$items = $order->get('items');

$c_currency = $this->helper->currency->current('code_3');
$hasShippingAddress = $this->helper->order->hasShippingAddress($order->get('id'));

$html 		= '';
$dispatcher = $this->helper->core->loadPlugins();
$dispatcher->trigger('onParseViewTemplate', array('com_sellacious.order.print', $order, &$html));
?>
<script>
	jQuery(function ($) {
		$(document).ready(function () {
			window.print();
		});
	});
</script>
<?php echo $html; ?>
