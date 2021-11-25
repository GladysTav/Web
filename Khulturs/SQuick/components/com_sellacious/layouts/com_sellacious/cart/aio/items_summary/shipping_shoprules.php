<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array    $displayData */
/** @var  array    $shipping_shoprules */
/** @var  integer  $quantity */

extract($displayData);

$helper     = SellaciousHelper::getInstance();
$g_currency = $helper->currency->getGlobal('code_3');

foreach ($shipping_shoprules as $ri => $rule)
{
	if (abs($rule->change) >= 0.01)
	{
		// $rule = {level, title, percent, amount, input, change, output};
		?>
		<div class="cart-item-attr">
			<div class="cart-item-attr-label">
				<?php echo $this->escape($rule->title) ?> @ <?php echo $rule->percent ? sprintf('%s%%', number_format($rule->amount, 2)) :
					$helper->currency->display($rule->amount, $g_currency, '', true); ?>
			</div>
			<div class="cart-item-attr-value">
				<?php
				$iChangeU = $helper->currency->display(abs($rule->change), $g_currency, '', true);
				$iChange  = $helper->currency->display(abs($rule->change) * $item->getQuantity(), $g_currency, '', true);

				echo sprintf('%s x %s = <strong>%s</strong>', $iChangeU, $item->getQuantity(), $iChange);
				?>
			</div>
		</div>
		<?php
	}
}
