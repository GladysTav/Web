<?php
/**
 * @version     2.0.0
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni Thakur <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Media\Image\ImageHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * @var  String  $mainCategoryAlignment
 */

JHtml::_('jquery.framework');
JHtml::_('ctech.bootstrap');

JHtml::_('stylesheet', 'mod_sellacious_categories/style.css', null, true);
?>
<div class="mod-sellacious-categories-grid <?php echo $classSfx; ?> ctech-wrapper">
    <div class="mod-categories-container">
        <?php
        $category = reset($mainCategory) ?: array_shift($categoryList);

        $image = ImageHelper::getImage('categories', $category->id, 'images');

        if (!$image)
		{
			$image = ImageHelper::getBlank('categories', 'images');
		}

        $imageUrl = $image->getUrl();
        $url = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id); ?>
        <div class="left-panel ctech-float-<?php echo $mainCategoryAlignment; ?>">
            <div class="mod-category-block block-left">
                <div class="image align-left-right">
                    <a href="<?php echo $url; ?>">
                        <img src="<?php echo $imageUrl; ?>">
                    </a>
                </div>
                <div class="back-overlay-image"></div>

                <div class="desc-product">
                    <div class="title"><a href="<?php echo $url; ?>"><?php echo $category->title; ?></a></div>

                    <?php if (isset($category->product_count) || isset($category->subcat_count)): ?>
                        <div class="item-counts-strip">
                            <div class="tip-top"><?php
                                if (isset($category->product_count)) {
                                    echo JText::plural('MOD_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $category->product_count);
                                }
                                ?>
							</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="right-panel">

            <?php
			$categoryList = array_slice($categoryList, 0, 8);
			foreach ($categoryList as $category) :
                $image = $helper->media->getImage('categories.images', $category->id);
                $url   = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id);?>
                <div class="mod-category-block block-right">
                    <div class="image align-left-image">
                        <a href="<?php echo $url; ?>">
                            <img src="<?php echo $image; ?>">
                        </a>
                    </div>
                    <div class="back-overlay-image"> </div>

                    <div class="desc-product">
                        <div class="title"><a href="<?php echo $url; ?>"><?php echo $category->title; ?></a></div>

                        <?php if (isset($category->product_count) || isset($category->subcat_count)): ?>
                            <div class="item-counts-strip">
                                <div class="tip-top"><?php
                                    if (isset($category->product_count))
                                    {
                                        echo JText::plural('MOD_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $category->product_count);
                                    }
                                    ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
