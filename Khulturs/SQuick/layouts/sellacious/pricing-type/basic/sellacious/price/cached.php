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

/** @var  stdClass  $displayData */
$item       = $displayData;
$helper     = SellaciousHelper::getInstance();
$c_currency = $helper->currency->current('code_3');
?>
<span class="hasTooltip" data-placement="right" title="<?php echo $item->seller_currency ?>">
					<?php echo $helper->currency->display($item->sales_price, $item->seller_currency, null, true); ?></span><br><?php

if ($item->seller_currency != $c_currency): ?>
	<small class="hasTooltip" data-placement="right" title="<?php echo $c_currency ?>">
		<?php echo $helper->currency->display($item->sales_price, (string) $item->seller_currency, $c_currency, true); ?></small><?php
endif;
