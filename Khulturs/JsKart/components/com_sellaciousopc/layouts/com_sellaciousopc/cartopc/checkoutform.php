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

$html = $displayData["data"]["html"];

JHtml::_('stylesheet', 'com_sellaciousopc/fe.layout.checkoutform.css', null, true);
JHtml::_('script', 'com_sellaciousopc/fe.layout.checkoutform.js', false, true);

if (!empty($html)): ?>
	<div id="cart-opc-checkoutform" class="cart-opc hide-section">
		<div class="legend"><?php echo JText::_('COM_SELLACIOUSOPC_CART_CHECKOUT_CHECKOUTFORM_LABEL') ?></div>
		<div class="clearfix"></div>
		<div id="checkoutform-editor">
			<form id="checkoutform-container">
				<?php echo $html;?>
			</form>
		</div>
		<div class="clearfix"></div>
		<div class="section-overlay"></div>
	</div>
<?php endif;
