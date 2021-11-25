<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

/** @var  \Sellacious\Report\SellerReport $this */
$c_currency = $this->helper->currency->current('code_3');
?>
<ul id="sparks" class="pull-right report-summary">
	<li></li>
	<li class="sparks-info span-0">
		<h5>
			<?php echo JText::_('PLG_SYSTEM_SELLACIOUSREPORTSCART_SUMMARY_TOTAL_SALE_VALUE'); ?>
			<span class="txt-color-greenDark">
				<?php echo $this->helper->currency->display($displayData['total_sale_value'], $c_currency, null); ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('PLG_SYSTEM_SELLACIOUSREPORTSCART_SUMMARY_TOTAL_ORDER_NO'); ?><span class="txt-color-greenDark">
				<i class="fa fa-shopping-cart"></i>&nbsp;<?php
				echo $displayData['total_order_no']; ?>
			</span>
		</h5>
	</li>
	<li></li>
</ul>

<div class="clearfix"></div>
<hr class="thin-line">

