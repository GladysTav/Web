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

/** @var stdClass[] $displayData */
foreach ($displayData as $i => $address)
{
	$body    = JLayoutHelper::render('com_sellacious.user.address.form', $address);
	$options = array(
		'backdrop' => 'static'
	);
	$footer  = '<button type="button" class="ctech-btn ctech-btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUS_PRODUCT_UPDATE') . '</button>';

	echo JHtml::_('ctechBootstrap.modal', 'address-form-' . (int) $address->id, JText::_('COM_SELLACIOUS_CART_USER_ADDRESS_FORM_EDIT_TITLE'), $body, $footer, $options);
}
