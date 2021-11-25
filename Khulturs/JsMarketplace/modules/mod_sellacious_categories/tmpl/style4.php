<?php
/**
 * @version     1.7.4
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni Thakur <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'mod_sellacious_categories/style.css', null, true);
?>
<div class="categories-style-four <?php echo $classSfx; ?>">
    <div class="mod-categories-container">
        <?php foreach ($categoryList as $category) :
            $image = $helper->media->getImage('categories.images', $category->id);
            $url   = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id);?>
            <div class="style-four-block">
                <div class="title"><a href="<?php echo $url; ?>"><p><?php echo $category->title; ?></p></a></div>
                <div class="desc-product">
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
                <div class="image">
                    <a href="<?php echo $url; ?>">
                        <img src="<?php echo $image; ?>">
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="clearfix"></div>
</div>
