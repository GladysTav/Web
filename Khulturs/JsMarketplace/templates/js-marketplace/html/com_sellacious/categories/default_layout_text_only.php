<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass  $tplData */
$item   = $tplData;

/** @var  SellaciousViewCategories $this */

$paths              = $this->helper->category->getImages($item->id, true);
$suffixStore        = $this->state->get('stores.id') ? '&store_id=' . $this->state->get('stores.id') : '';
$suffixManufacturer = $this->state->get('manufacturers.id') ? '&manufacturer_id=' . $this->state->get('manufacturers.id') : '';
$url                = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $item->id . $suffixStore . $suffixManufacturer);

$catCols = $this->helper->config->get('category_cols', 4);
$sellCats = '';
if ($catCols == '4'):
	$sellCats = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '3'):
	$sellCats = 'sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '2'):
	$sellCats = 'sell-col-xs-6';
elseif ($catCols == 'auto' ):
	$sellCats = 'auto-adjust';
endif;

?>
<div class="layout-two category-cols <?php echo $sellCats; ?>">
		<div class="desc-product">
			<a href="<?php echo $url ?>">
			<div>
				<span class="title"><?php echo $item->title; ?></span>
			</div>

			<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
				<br />
				<ul class="item-counts-strip">
					<li class="tip-product">
						<?php $prod_count= $item->product_count;
						if ($prod_count!=0)
						{
							echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_LAYOUT_2_COUNT_N', $item->product_count);
						}?>
					</li>
					<li class="tip-category"><?php
						$cat_count = $item->subcat_count;
						if ($cat_count != 0)
						{

							echo JText::plural('TEMP_COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count);
						}

						?></li>
				</ul>
			<?php endif; ?>
			</a>
		</div>


</div>
