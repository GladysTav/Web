<?php
/**
 * @version     2.0.0
 * @package     Sellacious Products Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="mod-product-<?php $params->get('class_sfx'); ?>">

	<?php if ($params->get('section_title')): ?>
		<h3 class="mod-products-heading"><?php echo $params->get('section_title'); ?></h3>
	<?php endif; ?>

	<?php if($params->get('section_desc')): ?>
		<p class="mod-products-subheading"><?php echo $params->get('section_desc'); ?></p>
	<?php endif; ?>

	<?php
	$args = array(
		'items'       => $products,
		'params'      => $params,
		'context'     => array('module', 'products', 'grid'),
		'id'          => $module->id,
		'layout'      => $params->get('layout', 'grid'),
		'blockLayout' => $params->get('product_block_layout', 'elegant')
	);

	echo JLayoutHelper::render('sellacious.product.grid', $args);
	?>
</div>
