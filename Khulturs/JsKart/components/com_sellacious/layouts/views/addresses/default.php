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

/** @var SellaciousViewAddresses $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');
JHtml::_('ctech.select2');

JHtml::_('script', 'com_sellacious/plugin/serialize-object/jquery.serialize-object.min.js', false, true);
JHtml::_('script', 'com_sellacious/fe.view.addresses.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.addresses.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JText::script('COM_SELLACIOUS_USER_CONFIRM_ADDRESS_REMOVE_MESSAGE');

$user = JFactory::getUser();
?>
<div class="ctech-wrapper">
	<div class="addresses-container">
		<div class="address-heading">
			<h2><?php echo JText::_('COM_SELLACIOUS_USER_PROFILE_ADDRESS') ?>
				<a href="#address-form-0" role="button" data-toggle="ctech-modal"
				   class="ctech-mb-3 btn-add-address ctech-float-right ctech-text-primary"><i class="fa fa-plus"></i> <span
							class="add-address-text"><?php echo JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'); ?></span></a></h2>
		</div>
		<div id="addresses" class="cart-aio ctech-text-center">
			<div id="address-editor">
				<ul id="address-items"></ul>
				<div id="address-modals"></div>
				<?php
				$body    = JLayoutHelper::render('com_sellacious.user.address.form');
				$options = array(
					'backdrop' => 'static',
				);
				$footer  = '<button type="button" class="ctech-btn ctech-btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_SAVE') . '</button>';

				echo JHtml::_('ctechBootstrap.modal', 'address-form-0', JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_ADD_TITLE'), $body, $footer, $options);
				?>
				<div class="ctech-clearfix"></div>
			</div>
			<div class="ctech-clearfix"></div>
		</div>
	</div>

	<?php echo JHtml::_('form.token'); ?>
</div>
