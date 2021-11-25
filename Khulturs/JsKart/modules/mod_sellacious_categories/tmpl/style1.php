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
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'mod_sellacious_categories/style.css', null, true);
?>
<div class="mod-sellacious-categories-style-one <?php echo $classSfx; ?>">
    <div class="mod-categories-container">
            <?php foreach ($categoryList as $category) :
                $image = $helper->media->getImage('categories.images', $category->id);
                $url   = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id);?>
                <div class="mod-category-style-one-block">
                    <div class="image">
                        <a href="<?php echo $url; ?>">
                            <img src="<?php echo $image; ?>">
                        </a>
                    </div>
                    <div class="desc-product">
                        <div class="title"><a href="<?php echo $url; ?>" title="<?php echo $category->title; ?>"><?php echo $category->title; ?></a></div>

                        <?php if (isset($category->product_count) || isset($category->subcat_count)): ?>
                            <div class="item-counts-strip">
                                <div class="tip-top">

									<?php if (isset($category->product_count)):
                                        echo JText::plural('MOD_SELLACIOUS_CATEGORY_PRODUCT_COUNT_N', $category->product_count);
                                    endif; ?>
								</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
    </div>
    <div class="clearfix"></div>
</div>
