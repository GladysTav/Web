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

/** @var  SellaciousViewOrders  $this */
/** @var  stdClass[]  $tplData */
$items = $tplData;

foreach ($items as $oi):
	$options = array(
		'backdrop' => 'static',
	);

	if ($oi->return_available)
	{
		/** @var  JForm  $form */
		$form = JForm::getInstance('com_sellacious.order.return_modal', 'return_modal', array('control' => 'jform'));

		$form->setValue('order_id', '', $oi->order_id);
		$form->setValue('item_uid', '', $oi->item_uid);

		$args        = new stdClass;
		$args->form  = $form;
		$args->data  = $oi;
		$args->task  = 'order.placeReturn';
		$args->label = JText::_('COM_SELLACIOUS_ORDER_PLACE_RETURN_BUTTON_LABEL');

		$body = JLayoutHelper::render('com_sellacious.order.request_modal', $args);
		echo JHtml::_('ctechBootstrap.modal', 'return-form-' . $oi->id, trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- '), $body, '', $options);
	}

	if ($oi->exchange_available)
	{
		/** @var  JForm  $form */
		$form = JForm::getInstance('com_sellacious.order.exchange_modal', 'exchange_modal', array('control' => 'jform'));

		$form->setValue('order_id', '', $oi->order_id);
		$form->setValue('item_uid', '', $oi->item_uid);

		$args        = new stdClass;
		$args->form  = $form;
		$args->data  = $oi;
		$args->task  = 'order.placeExchange';
		$args->label = JText::_('COM_SELLACIOUS_ORDER_PLACE_EXCHANGE_BUTTON_LABEL');

		$body = JLayoutHelper::render('com_sellacious.order.request_modal', $args);
		echo JHtml::_('ctechBootstrap.modal', 'exchange-form-' . $oi->id, trim(sprintf('%s - %s', $oi->product_title, $oi->variant_title), '- '), $body, '', $options);
	}
endforeach;
