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

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/plugin/serialize-object/jquery.serialize-object.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.order-payment-address.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.order-payment-address.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JText::script('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');
JText::script('COM_SELLACIOUS_ORDER_ADDRESS_SHIPPING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUS_ORDER_ADDRESS_BILLING_EMPTY_MESSAGE');
JText::script('COM_SELLACIOUS_ORDER_ADDRESSES_EMPTY_MESSAGE');

$user = JFactory::getUser();
?>
<div id="address-container">
	<hr style="margin: 5px 0 0 0;">
	<div id="address-editor">
		<div id="addresses">
			<ul id="address-items"></ul>
			<div id="address-modals"></div>
			<?php
			$body    = JLayoutHelper::render('com_sellacious.user.address.form');
			$options = array('backdrop' => 'static');
			$footer  = '<button type="button" class="ctech-btn ctech-btn-primary ctech-btn-sm btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_SAVE') . '</button>';

			echo JHtml::_('ctechBootstrap.modal', 'address-form-0', JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'), $body, $footer, $options);
			?>
			<div class="clearfix"></div>
			<div class="margin-top-10" id="address-toolbar">
				<a href="#address-form-0" role="button" data-toggle="ctech-modal"
				   class="ctech-btn ctech-btn-sm ctech-btn-default ctech-text-primary btn-add-address ctech-float-left">
					<i class="fa fa-plus"></i> <?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'); ?></a>
				<button class="ctech-btn ctech-btn-sm ctech-btn-default ctech-text-secondary btn-save ctech-float-right">
					<i class="fa fa-save"></i> <?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_SAVE_TITLE'); ?></button>
			</div>
			<div class="ctech-clearfix"></div>
		</div>
	</div>
	<input type="hidden" id="address-billing">
	<input type="hidden" id="address-shipping">
	<input type="hidden" id="order-id" value="<?php echo $this->item->id ?>">
</div>
