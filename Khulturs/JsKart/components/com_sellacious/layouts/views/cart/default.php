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

/** @var SellaciousViewCart $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/util.cart.aio.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.cart.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.cart.css', null, true);

JText::script('COM_SELLACIOUS_CART_CONFIRM_CLEAR_CART_ACTION_MESSAGE');

$user      = JFactory::getUser();
$arg       = new stdClass;
$arg->cart = $this->cart;
?>
<div class="ctech-wrapper">
	<div class="w100p" id="cart-wrapper">
		<div id="cart-container">
			<!-- Fake modal div -->
			<div id="cart-items">
				<?php $layout = 'com_sellacious.cart.aio.' . ($this->cart->count() == 0 ? 'empty' : 'items_modal'); ?>
				<?php echo JLayoutHelper::render($layout, $arg); ?>
			</div>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</div>
