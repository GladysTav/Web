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

use Joomla\Registry\Registry;

/** @var  SellaciousViewOrder $this */
$order           = new Registry($this->item);
$items           = $order->get('items');
$orderParams     = new Registry($order->get('params'));
$shipping_params = array_filter((array)$order->get('shipping_params'), function ($var) {
	$var        = (array)$var;
	$foundEmpty = array();

	if (empty($var))
	{
		$foundEmpty[] = false;
	}
	else
	{
		foreach ($var as $item)
		{
			if (empty($item))
			{
				$foundEmpty[] = false;
			}
		}
	}

	return empty($foundEmpty);
});

$itemisedShipping            = $order->get('shipping_rule_id') == 0;
$sellerShippingRules         = array_filter((array) $order->get('seller_shipping_rules'));
$sellerwiseShippingInProduct = $orderParams->get('product_select_shipping', 0) == 1;

if (!empty($shipping_params)): ?>
	<div class="order-additional-questions">
		<h5 class="additional-info-title"><?php echo JText::_('COM_SELLACIOUS_ORDER_ADDITIONAL_INFO_SHIPPING_TITLE'); ?></h5>
		<?php
		if (count($sellerShippingRules) > 0):
			$sellerShippingRules = new Registry($sellerShippingRules);

			if ($sellerwiseShippingInProduct):
				foreach ($shipping_params as $sellerUid => $sellerItems):
					foreach ($sellerItems as $itemUid => $params):
						if (!$params)
						{
							continue;
						}

						$product_name = '';
						$shippingRule = $sellerShippingRules->get($sellerUid . '.' . $itemUid, null);

						foreach ($items as $oi)
						{
							if ($oi->item_uid === $itemUid)
							{
								$product_name = $oi->product_title;
								break;
							}
						}
						?>
						<div><small><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_ITEMISED_SHIPPING_QUESTION_FOR', $product_name . ' (' . $shippingRule->sellerName . ')'); ?></small></div><?php
						foreach ($params as $q): ?>
							<div class="additional-param">
							<span class="additional-param-label"><?php echo $q->label; ?></span>
							<span class="additional-param-value"><?php echo $q->value; ?></span>
							</div><?php
						endforeach;
					endforeach;
				endforeach;
			else:
				foreach ($shipping_params as $sellerUid => $params):
					if (!$params)
					{
						continue;
					}

					$shippingRule = $sellerShippingRules->get($sellerUid, null);

					if (isset($shippingRule)): ?>
						<div><small><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_ITEMISED_SHIPPING_QUESTION_FOR', $shippingRule->sellerName); ?></small></div><?php
					endif;

					foreach ($params as $q): ?>
						<div class="additional-param">
						<span class="additional-param-label"><?php echo $q->label; ?></span>
						<span class="additional-param-value"><?php echo $q->value; ?></span>
						</div><?php
					endforeach;
				endforeach;
			endif;
		else:
			foreach ($shipping_params as $p => $param):
				if (!$param)
				{
					continue;
				}

				if (!$itemisedShipping):
					?>
					<div class="additional-param">
						<span class="additional-param-label"><?php echo $param->label; ?></span>
						<span class="additional-param-value"><?php echo $param->html; ?></span>
					</div>
				<?php else:
					foreach ($param as $q):
						$product_name = '';

						foreach ($items as $oi)
						{
							if ($oi->item_uid === $p)
							{
								$product_name = $oi->product_title;
								break;
							}
						}
						?>
						<div class="additional-param">
							<span class="additional-param-label"><?php echo $q->label; ?></span>
							<span class="additional-param-value"> <?php echo $q->value; ?></span><br />
							<small class="additional-param-product-name"><?php echo JText::sprintf('COM_SELLACIOUS_ORDER_ITEMISED_SHIPPING_QUESTION_FOR', $product_name); ?></small>
						</div>
					<?php endforeach;
				endif;
			endforeach;
		endif;
		?>
	</div>
<?php endif; ?>

