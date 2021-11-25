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

$columns = $displayData["columns"];
$cart    = $displayData["cart"];

$allSections = array();

$helper      = SellaciousHelper::getInstance();
$opc_columns = $helper->config->get('opc_column_count');
?>
<div class="ctech-wrapper">
	<div class="ctech-clearfix"></div>
	<div class="ctech-row sellacious-opc">
		<?php
		$opcSections = array();
		foreach ($columns as $column => $sections)
		{
			foreach ($sections as $section => $data)
			{
				$allSections[$section] = $data;
			}
		}

		foreach ($allSections as $section => $data)
		{
			$allSections[$section] = $data;

			$opcSections[$section] = array(
				'enabled' => $data["enabled"],
				'html'    => $data["html"],
				'params'  => array(
					"data" => $data,
					"cart" => $cart
				)
			);
		} ?>

		<div class="ctech-col-lg-<?php echo $opc_columns == 2 ? '6' : '4' ?> ctech-col-sm-6 ctech-col-xs-12 lay-1">
			<?php
			echo $opcSections['account']['enabled'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.account', $opcSections['account']['params'], '', array('debug' => 0)) : "";
			echo $opcSections['address']['enabled'] && $opcSections['address']['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.address', $opcSections['address']['params'], '', array('debug' => 0)) : "";
			?>
		</div>
		<div class="ctech-col-lg-<?php echo $opc_columns == 2 ? '6' : '4' ?> ctech-col-sm-6 ctech-col-xs-12 lay-2">
			<?php
			echo $opcSections['shipment']['enabled'] && $opcSections['shipment']['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.shipment', $opcSections['shipment']['params'], '', array('debug' => 0)) : JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $payment_params, '', array('debug' => 0));
			echo $opcSections['checkoutform']['enabled'] && $opcSections['checkoutform']['html'] ? JLayoutHelper::render('com_sellaciousopc.cartopc.checkoutform', $opcSections['checkoutform']['params'], '', array('debug' => 0)) : "";
			if ($opc_columns == 2):
				echo ($opcSections['payment']['enabled'] && $opcSections['payment']['html']) && ($opcSections['shipment']['enabled'] && $opcSections['shipment']['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $opcSections['payment']['params'], '', array('debug' => 0)) : "";
				echo ($opcSections['summary']['enabled'] && $opcSections['summary']['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.summary', $opcSections['summary']['params'], '', array('debug' => 0)) : "";
			endif;
			?>
		</div>
		<?php if ($opc_columns == 3): ?>
			<div class="ctech-col-lg-4 ctech-col-sm-12 lay-3">
				<?php
				echo ($opcSections['payment']['enabled'] && $opcSections['payment']['html']) && ($opcSections['shipment']['enabled'] && $opcSections['shipment']['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.payment', $opcSections['payment']['params'], '', array('debug' => 0)) : "";
				echo ($opcSections['summary']['enabled'] && $opcSections['summary']['html']) ? JLayoutHelper::render('com_sellaciousopc.cartopc.summary', $opcSections['summary']['params'], '', array('debug' => 0)) : "";
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
