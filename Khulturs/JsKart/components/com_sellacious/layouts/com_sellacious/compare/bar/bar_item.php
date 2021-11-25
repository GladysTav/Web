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

/** @var  \Sellacious\Product  $displayData */
$item   = $displayData;
$url    = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->getCode());
$images = $item->getImages(true, true);
?><div class="w100p">
	<div class="compare-item">
		<span class="compare-item-image" style="background-image: url('<?php echo reset($images) ?>')" title="<?php echo htmlspecialchars($item->get('title')) ?>"></span>
		<div class="compare-item-title">
			<a href="<?php echo $url ?>">
				<?php echo htmlspecialchars($item->get('title') . ' ' . $item->get('variant_title')) ?>
			</a>
		</div>
		<a class="compare-remove ctech-text-white ctech-rounded-circle ctech-bg-danger ctech-d-inline-block" href="#" data-item="<?php echo $item->getCode() ?>"><i class="fa fa-times"></i></a>
	</div>
</div><?php



