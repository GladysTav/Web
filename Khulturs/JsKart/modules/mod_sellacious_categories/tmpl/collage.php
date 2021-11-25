<?php
/**
 * @version     2.0.0
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Media\Image\ImageHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * @var  \stdClass  $categories
 */

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'mod_sellacious_categories/collage.css', null, true);

$collageCategories = array();

try
{
	foreach ($categories as $i => $cat)
	{
		$thisCategory = ModSellaciousCategoriesHelper::getCategories(array($categories->$i->category));
		$collageCategories[] = reset($thisCategory);
	}
}
catch (Exception $e)
{
}

$g_currency = $helper->currency->getGlobal('code_3');
$c_currency = $helper->currency->current('code_3');
?>

<div class="mod-sellacious-categories categories-collage ctech-wrapper">
	<div class="mod-categories-wrapper">
		<div class="categories-collage-left-panel">
			<div class="left-panel-top" style="">
				<?php
				$image = $categories->collage_categories0->cat_image ?: ImageHelper::getImage('categories', $collageCategories[0]->id, 'images');
				if (!$image) $image = ImageHelper::getBlank('categories');
				if (is_object($image)) $image = $image->getUrl();
				$url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $collageCategories[0]->id);
				?>
				<div class="category-block-wrapper">
					<span class="category-block-image" style="background-image: url('<?php echo $image; ?>')"></span>
					<div class="category-block-info">
						<h4><?php echo $categories->collage_categories0->cat_name ?: $collageCategories[0]->title; ?></h4>
						<div class="category-info">
							<div class="category-info-block">
								<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_PRODUCTS_COUNT_LABEL'); ?></div>
								<h4><?php echo $collageCategories[0]->product_count; ?></h4>
							</div>
							<?php if ($categories->collage_categories0->starting_price): ?>
								<div class="category-info-block">
									<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_FROM_PRICE_LABEL'); ?></div>
									<h4><?php echo $helper->currency->display($categories->collage_categories0->starting_price, $g_currency, $c_currency, true); ?></h4>
								</div>
							<?php endif; ?>
						</div>
						<div class="category-link">
							<a class="ctech-btn ctech-border-white ctech-rounded-0" href="<?php echo $url; ?>">
								<?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_CATEGORY_LINK'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div class="left-panel-bottom">
				<?php
				$image = $categories->collage_categories1->cat_image ?: ImageHelper::getImage('categories', $collageCategories[1]->id, 'images');
				if (!$image) $image = ImageHelper::getBlank('categories');
				if (is_object($image)) $image = $image->getUrl();
				$url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $collageCategories[1]->id);
				?>
				<div class="category-block-wrapper">
					<span class="category-block-image" style="background-image: url('<?php echo $image; ?>')"></span>
					<div class="category-block-info">
						<h4><?php echo $categories->collage_categories1->cat_name ?: $collageCategories[1]->title; ?></h4>
						<div class="category-info">
							<div class="category-info-block">
								<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_PRODUCTS_COUNT_LABEL'); ?></div>
								<h4><?php echo $collageCategories[1]->product_count; ?></h4>
							</div>
							<?php if ($categories->collage_categories1->starting_price): ?>
								<div class="category-info-block">
									<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_FROM_PRICE_LABEL'); ?></div>
									<h4><?php echo $helper->currency->display($categories->collage_categories1->starting_price, $g_currency, $c_currency, true); ?></h4>
								</div>
							<?php endif; ?>
						</div>
						<div class="category-link">
							<a class="ctech-btn ctech-border-white ctech-rounded-0" href="<?php echo $url; ?>">
								<?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_CATEGORY_LINK'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="categories-collage-right-panel">
			<div class="right-panel-top">
				<div class="right-panel-top-block">
					<?php
					$image = $categories->collage_categories2->cat_image ?: ImageHelper::getImage('categories', $collageCategories[2]->id, 'images');
					if (!$image) $image = ImageHelper::getBlank('categories');
					if (is_object($image)) $image = $image->getUrl();
					$url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $collageCategories[2]->id);
					?>
					<div class="category-block-wrapper">
						<span class="category-block-image" style="background-image: url('<?php echo $image; ?>')"></span>
						<div class="category-block-info">
							<h4><?php echo $categories->collage_categories2->cat_name ?: $collageCategories[2]->title; ?></h4>
							<div class="category-info">
								<div class="category-info-block">
									<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_PRODUCTS_COUNT_LABEL'); ?></div>
									<h4><?php echo $collageCategories[2]->product_count; ?></h4>
								</div>
								<?php if ($categories->collage_categories2->starting_price): ?>
									<div class="category-info-block">
										<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_FROM_PRICE_LABEL'); ?></div>
										<h4><?php echo $helper->currency->display($categories->collage_categories2->starting_price, $g_currency, $c_currency, true); ?></h4>
									</div>
								<?php endif; ?>
							</div>
							<div class="category-link">
								<a class="ctech-btn ctech-border-white ctech-rounded-0" href="<?php echo $url; ?>">
									<?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_CATEGORY_LINK'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="right-panel-top-block">
					<?php
					$image = $categories->collage_categories3->cat_image ?: ImageHelper::getImage('categories', $collageCategories[3]->id, 'images');
					if (!$image) $image = ImageHelper::getBlank('categories');
					if (is_object($image)) $image = $image->getUrl();
					$url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $collageCategories[3]->id);
					?>
					<div class="category-block-wrapper">
						<span class="category-block-image" style="background-image: url('<?php echo $image; ?>')"></span>
						<div class="category-block-info">
							<h4><?php echo $categories->collage_categories3->cat_name ?: $collageCategories[3]->title; ?></h4>
							<div class="category-info">
								<div class="category-info-block">
									<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_PRODUCTS_COUNT_LABEL'); ?></div>
									<h4><?php echo $collageCategories[3]->product_count; ?></h4>
								</div>
								<?php if ($categories->collage_categories3->starting_price): ?>
									<div class="category-info-block">
										<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_FROM_PRICE_LABEL'); ?></div>
										<h4><?php echo $helper->currency->display($categories->collage_categories3->starting_price, $g_currency, $c_currency, true); ?></h4>
									</div>
								<?php endif; ?>
							</div>
							<div class="category-link">
								<a class="ctech-btn ctech-border-white ctech-rounded-0" href="<?php echo $url; ?>">
									<?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_CATEGORY_LINK'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="right-panel-bottom">
				<?php
				$image = $categories->collage_categories4->cat_image ?: ImageHelper::getImage('categories', $collageCategories[4]->id, 'images');
				if (!$image) $image = ImageHelper::getBlank('categories');
				if (is_object($image)) $image = $image->getUrl();
				$url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $collageCategories[4]->id);
				?>
				<div class="category-block-wrapper">
					<span class="category-block-image" style="background-image: url('<?php echo $image; ?>')"></span>
					<div class="category-block-info">
						<h4><?php echo $categories->collage_categories4->cat_name ?: $collageCategories[4]->title; ?></h4>
						<div class="category-info">
							<div class="category-info-block">
								<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_PRODUCTS_COUNT_LABEL'); ?></div>
								<h4><?php echo $collageCategories[4]->product_count; ?></h4>
							</div>
							<?php if ($categories->collage_categories4->starting_price): ?>
								<div class="category-info-block">
									<div><?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_FROM_PRICE_LABEL'); ?></div>
									<h4><?php echo $helper->currency->display($categories->collage_categories4->starting_price, $g_currency, $c_currency, true); ?></h4>
								</div>
							<?php endif; ?>
						</div>
						<div class="category-link">
							<a class="ctech-btn ctech-border-white ctech-rounded-0" href="<?php echo $url; ?>">
								<?php echo JText::_('MOD_SELLACIOUS_CATEGORIES_CATEGORY_LINK'); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
