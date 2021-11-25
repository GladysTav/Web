<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
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
$sellCats = '';
if ($catCols == '4'):
	$sellCats = 'sell-col-md-3 sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '3'):
	$sellCats = 'sell-col-sm-4 sell-col-xs-6';
elseif ($catCols == '2'):
	$sellCats = 'sell-col-xs-6';
elseif ($catCols == 'auto'):
	$sellCats = 'auto-adjust';
endif;

?>
<ul class="layout-menu-view  <?php echo $sellCats; ?>">
	<li class="desc-product">
		<a href="<?php echo $url ?>">
				<span class="title">
					<?php echo $item->title;

					if ($this->helper->config->get('show_icon_cat_menu_layout') == '1')
					{ ?>
						<small class="icon">
							<img src="<?php echo $this->helper->media->getImage('categories.icon', $item->id, true, true); ?>">
						</small>
					<?php }
					$prod_count = $item->product_count;
					if ($prod_count != 0)
					{
					?>
					<small>
						<?php
						echo JText::plural('COM_SELLACIOUS_CATEGORY_PRODUCT_LAYOUT_2_COUNT', $item->product_count);
						}
						?>
					</small>
				</span>
		</a>

		<div class="right-block">
			<?php
			$show_child_count = $this->helper->config->get('show_Level_subcat_menu_layout');
			$this->getCategoryMenu($item->id, $item->level, $show_child_count);
			?>


			<?php if (isset($item->product_count) || isset($item->subcat_count)): ?>
				<ul class="item-counts-strip">
					<?php if ($prod_count != 0 && $item->subcat_count != 0) { ?>
						<li class="line-li"></li>
					<?php } ?>
					<li class="tip-category"><?php
						$cat_count = $item->subcat_count;
						if ($cat_count != 0)
						{
							echo JText::plural('TEMP_COM_SELLACIOUS_CATEGORY_SUBCATEGORIES_COUNT_N', $item->subcat_count);
						}

						?></li>
				</ul>
			<?php endif; ?>
		</div>

	</li>

</ul>



