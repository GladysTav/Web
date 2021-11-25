<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var stdClass[] $displayData */
foreach ($displayData as $i => $address)
{
	$body    = JLayoutHelper::render('com_sellaciousopc.user.address.form', $address);
	$footer  = '<button type="button" class="ctech-btn ctech-btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUSOPC_PRODUCT_UPDATE') . '</button>';
	$options = array(
		'backdrop' => 'static',
		'scrollable' => true
	);

	echo JHtml::_('ctechBootstrap.modal', 'address-form-' . (int) $address->id, JText::_('COM_SELLACIOUSOPC_CART_USER_ADDRESS_FORM_EDIT_TITLE'), $body, $footer, $options);
}
