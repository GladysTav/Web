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

/** @var  stdClass $tplData */
$item = $tplData;

/** @var  SellaciousViewCategories $this */

$paths              = $this->helper->category->getImages($item->id, true);
$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';
$url                = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item->id . $suffixStore . $suffixManufacturer);

$catCols  = $this->helper->config->get('category_cols', 4);
$categoryClassName = '';
if ($catCols == '4')
{
	$categoryClassName = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
}
elseif ($catCols == '3')
{
	$categoryClassName = 'sell-col-sm-4 sell-col-xs-6';
}
elseif ($catCols == '2')
{
	$categoryClassName = 'sell-col-xs-6';
}
elseif ($catCols == 'auto')
{
	$categoryClassName = 'auto-adjust';
}

?>
<div class="layout-three category-cols <?php echo $categoryClassName; ?>">
	<div class="desc-product">
		<a href="<?php echo $url ?>">
			<div class="icon left-block">
				<img src="<?php echo $this->helper->media->getImage('categories.icon', $item->id, true, true); ?>">
			</div>
			<div class="right-block">
				<h6 class="title"><?php echo $item->title; ?></h6>

				<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
					<ul class="item-counts-strip">
						<?php if ($item->product_count > 0): ?>
							<li class="tip-product">
									<?php echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_COUNT', $item->product_count); ?>
							</li>
						<?php endif; ?>
						<?php if ($item->subcat_count > 0): ?>
							<li class="tip-category">
									<?php echo JText::plural('TEMP_COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count); ?>
							</li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
		</a>
	</div>


</div>
