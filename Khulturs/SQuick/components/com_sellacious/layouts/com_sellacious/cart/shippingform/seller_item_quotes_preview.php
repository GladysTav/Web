<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass $displayData */
/** @var  Sellacious\Cart $cart */

$helper     = SellaciousHelper::getInstance();
$quote      = $displayData->quote;
$cart       = $displayData->cart;
$g_currency = $cart->getCurrency();
$c_currency = $helper->currency->current('code_3');

if (!empty($quote)):
	$total = $helper->currency->display($quote->total, $g_currency, $c_currency, true);
	?>
	<tr class="shipment-preview-totals">
		<td>&nbsp;</td>
		<td colspan="2"><?php echo $quote->ruleTitle . ': ' . $total; ?></td>
	</tr>
	<?php
	?>
<?php endif; ?>
