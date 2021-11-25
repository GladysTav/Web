<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

$columns = $displayData["columns"];
$cart    = $displayData["cart"];

$allSections = array();
?>
<div class="clearfix"></div>
<div class="row row-fluid sellacious-opc">
	<?php
	foreach ($columns as $column => $sections)
	{
		foreach ($sections as $section => $data)
		{
			$allSections[$section] = $data;
		}
	}

	foreach($allSections as $section => $data)
	{
		$allSections[$section] = $data;
		$$section = array(
			'enabled' => $data["enabled"],
			'html' => $data["html"]
		);

		$params_name = $section . '_params';
		$$params_name = array(
			"data" => $data,
			"cart" => $cart
		);

	} ?>

	<div class="col-lg-4 col-sm-6 col-xs-12 lay-1">
		<?php
		echo $account['enabled'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.account', $account_params, '', array('debug' => 0)) : "";
		echo $address['enabled'] && $address['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.address', $address_params, '', array('debug' => 0)) : "";
		?>
	</div>
	<div class="col-lg-4 col-sm-6 col-xs-12 lay-2">
		<?php
		echo $shipment['enabled'] && $shipment['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.shipment', $shipment_params, '', array('debug' => 0)) : JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $payment_params, '', array('debug' => 0));
		echo $checkoutform['enabled'] && $checkoutform['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.checkoutform', $checkoutform_params, '', array('debug' => 0)) : "";
		?>
	</div>
	<div class="col-lg-4 col-sm-12 lay-3">
		<?php
		echo ($payment['enabled'] && $payment['html']) && ($shipment['enabled'] && $shipment['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $payment_params, '', array('debug' => 0)) : "";
		echo ($summary['enabled'] && $summary['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.summary', $summary_params, '', array('debug' => 0)) : "";
		?>
	</div>
</div>
