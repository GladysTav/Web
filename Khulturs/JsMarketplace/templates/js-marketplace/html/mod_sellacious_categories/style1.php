<?php
/**
 * @version     1.7.3
 * @package     Sellacious Categories Module
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Chandni Thakur <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;

defined('_JEXEC') or die('Restricted access');

JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'mod_sellacious_categories/style.css', null, true);
?>
<div class="mod-sellacious-categories-style-one <?php echo $classSfx; ?>">
    <div class="mod-categories-container">
            <?php foreach ($categoryList as $category) :
                $image = ImageHelper::getImage('com_sellacious/categories', $category->id, 'images');
            	$image = ImageHelper::getResized(array($image), 150, 150, true, 85,  ResizeImage::RESIZE_FIT);
            	$image = ImageHelper::getUrls($image);

                $url   = JRoute::_('index.php?option=com_sellacious&view=categories&parent_id=' . $category->id);?>
                <div class="mod-category-style-one-block">
                    <div class="image">
                        <a href="<?php echo $url; ?>">
                            <img src="<?php echo reset($image); ?>">
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
