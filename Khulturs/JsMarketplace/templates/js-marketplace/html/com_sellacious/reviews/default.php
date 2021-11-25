<?php
/**
 * @version     1.7.3
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Product;

defined('_JEXEC') or die;

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);
JHtml::_('stylesheet', 'com_sellacious/util.bootstrap-progress.css', null, true);
/** @var SellaciousViewOrders $this */
$app  = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'com_sellacious/fe.view.orders.tile.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/util.rating.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.reviews.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

$reviews     = $this->items;
$stats   = $this->getReviewStats();
$link_detail = $this->helper->config->get('product_detail_page');
$rateable    = (array) $this->helper->config->get('allow_ratings_for');
$user = JFactory::getUser();
$code = $this->state->get('product.code');

$page_id = $this->getLayout() == 'modal' ? 'product_modal' : 'product';


?>

<!-- Breadcrumbs-->
<div class="page-brdcrmb">
    <?php
    jimport('joomla.application.module.helper');
    $modules = JModuleHelper::getModules('breadcrumbs');
    foreach ($modules as $module):
        $renMod = JModuleHelper::renderModule($module);

        if (!empty($renMod) && ($module->module == "mod_breadcrumbs")):?>
            <div class="<?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                <div class="moreinfo-box">
                    <?php
                    if ($module->showtitle == 1) { ?>
                        <h3><?php echo $module->title ?></h3>
                    <?php } ?>
                    <div class="innermoreinfo">
                        <div class="relatedinner">
                            <?php echo trim($renMod); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="product-bottom <?php echo (isset($module->class_sfx)) ? $module->class_sfx : ''; ?>">
                <?php echo trim($renMod); ?>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>


<?php if (!empty($this->seller->id)):
	$seller = new Joomla\Registry\Registry($this->seller);
	$logo   = $this->helper->media->getImage('sellers.logo', $seller->get('id'));
    $policies = $this->helper->field->getValueByName('profile', $seller->get('user_id'), 'policies-seller');
	$storeLink  = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $seller->get('user_id'));
    ?>
    <div id="seller-info">
		<div class="sellerdata">
			<a href="<?php echo $storeLink; ?>">
				<h2><?php echo $seller->get('store_name') ?: $seller->get('title') ?></h2>
			</a>
			<?php if ($this->helper->config->get('show_store_product_count') == '1' && $seller->get('product_count')): ?>
				<div class="product-count">
					<?php echo JText::plural('COM_SELLACIOUS_SELLER_PRODUCT_COUNT_N', $seller->get('product_count')); ?>
				</div>
			<?php endif; ?>
			<?php if ($seller->get('store_address')): ?>
				<div class="store-address"><?php echo nl2br($seller->get('store_address')) ?></div>
			<?php endif; ?>
			<?php if (in_array('seller', $rateable)): ?>
				<?php $stars = round($seller->get('rating.rating', 0) * 2); ?>
				<div class="product-rating rating-stars star-<?php echo $stars ?>">
					<?php echo number_format($seller->get('rating.rating', 0), 1) ?>
					<?php echo '<span> – </span>'; echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $seller->get('rating.count')); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="seller-logoarea">
			<img class="seller-logo" src="<?php echo $logo ?>"
				 alt="<?php echo htmlspecialchars($seller->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
		</div>
		<div class="clearfix"></div>
	</div>




    <div class="main-tab-product-col">

        <?php

        $ifpolicy		= $this->helper->field->getValueByName('profile', $seller->get('user_id'), 'policies-seller');
        $ifprodrev		= !empty($reviews);
        $ifrating	    = !empty($this->seller_reviews); ?>
        <?php if ($ifpolicy ||  $ifrating || $ifprodrev) : ?>
            <div class="main-tab-reviews">
                <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'rating')); ?>

                <?php if ($ifpolicy):?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'policy', JText::_('COM_SELLACIOUS_TITLE_POLICY', true)); ?>
                        <div class="policies mb-4">
                            <p><?php echo $policies ?></p>

                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif; ?>
                <?php if($ifrating):?>
                <?php echo  JHtml::_('bootstrap.addTab', 'myTab', 'rating', JText::_('COM_SELLACIOUS_SELLER_RATING', true));?>
                    <div class="innerdesc mb-4">
                        <div class="rating-box">
                            <div class="reviewslist">
                                <?php
                                foreach ($this->seller_reviews as $sreview):?>
                                <div class="sell-row nomargin">
                                    <div class="sell-col-xs-12 reviewtyped nopadd">
                                        <div class="review-title">
                                            <h3 class="pr-title"><?php echo $sreview->title ?></h3>
                                        </div>
                                    <div class="reviewauthor">
                                            <div class="rating-stars rating-stars-md star-<?php echo $sreview->rating * 2 ?>">
                                                <span class="starcounts"><?php echo number_format($sreview->rating, 1); ?></span>
                                                <div class="author">
                                                    <h4 class="pr-author"><?php echo $sreview->author_name ?></h4>
                                                    <?php if ($sreview->buyer == 1): ?>
                                                        <div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <div class="auth-det">
                                            <h5 class="pr-date"><?php echo JHtml::_('date', $sreview->created, 'M d, Y'); ?></h5>
                                        </div>

                                    </div>
                                    <div class="reviewtyped">
                                        <p class="pr-body"><?php echo $sreview->comment ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php
                            endforeach;
                            ?>
                        </div>
                    </div>

                <?php endif; ?>
                <?php echo JHtml::_('bootstrap.endTabSet'); ?>

            </div>
        <?php endif; ?>



    </div>
        <?php endif; ?>



<?php if ($this->state->get('filter.product_id', 0)):
	$productId = $this->state->get('filter.product_id', 0);
	$product   = new Product($productId);

	$productRating = new Registry($this->helper->rating->getProductRating($productId));
	$productImage  = $this->helper->product->getImage($productId);
	?>
<div class="box-reviews-head">
    <div class="width-40">
        <table class="rating-statistics">
            <tbody>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php
                $stat    = ArrayHelper::getValue($stats, $i, null);
                $count   = isset($stat->count) ? $stat->count : 0;
                $percent = isset($stat) ? ($stat->count / $stat->total * 100) : 0;
                ?>
                <tr>
                    <td class="nowrap" style="width:90px;">
                        <div class="rating-stars rating-stars-md star-<?php echo $i * 2 ?>">
                            &nbsp;<?php echo number_format($i, 1); ?></div>
                    </td>
                    <td class="nowrap rating-progress">
                        <div class="progress progress-sm">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?php echo $percent ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="nowrap" style="width:60px;"><?php echo $count; ?> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_RATINGS'); ?> </td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>

    </div>


    <div id="product-info" class="width-60">

        <div class="product-logoarea">
            <img class="product-logo" src="<?php echo $productImage ?>"
                 alt="<?php echo htmlspecialchars($product->get('title'), ENT_COMPAT, 'UTF-8'); ?>">
        </div>

            <div class="productdata">
                <a href="index.php?option=com_sellacious&view=product&p=<?php echo $product->getCode(); ?>">
                    <h2 class="product-title"><?php echo $product->get('title') ?></h2></a>
                <?php if (in_array('product', $rateable)): ?>
                    <?php $stars = round($productRating->get('rating', 0) * 2); ?>
                    <div class="product-rating rating-stars star-<?php echo $stars ?>">
                        <?php echo number_format($productRating->get('rating', 0), 1) ?>
                        <?php echo '<span> – </span>'; echo JText::plural('COM_SELLACIOUS_RATINGS_COUNT_N', $productRating->get('count')); ?>
                    </div>
                <?php endif; ?>
            </div>
        <div class="buy-container">
            <div id="buttons-wishlist">
                <?php
                if ($this->helper->config->get('product_wishlist')):
                    if ($user->guest):
                        $url   = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $code, false);
                        $login = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url), false); ?>
                    <a class="btn-wishlist" data-guest="true" data-href="<?php echo $this->escape($login) ?>">
                        <i class="fa fa-heart-o"></i><span> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?></span>
                        </a><?php
                    elseif ($this->helper->wishlist->check($code, null)):
                        $url = JRoute::_('index.php?option=com_sellacious&view=wishlist', false); ?>
                    <a class="btn-wishlist" data-href="<?php echo $this->escape($url) ?>">
                        <i class="fa fa-heart"></i><span> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADDED_TO_WISHLIST'); ?></span>
                        </a><?php
                    else: ?>
                    <a class="btn-wishlist" data-item="<?php echo $code ?>">
                        <i class="fa fa-heart-o"></i><span> <?php echo JText::_('COM_SELLACIOUS_PRODUCT_ADD_TO_WISHLIST'); ?></span>
                        </a><?php
                    endif;
                endif;
                ?>
            </div>
        </div>

        </div>



</div>



<?php endif; ?>

        <h3><?php echo JText::_('COM_SELLACIOUS_REVIEWS_PRODUCT'); ?></h3>

<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
	  method="post" name="adminForm" id="adminForm">
		<?php if (!empty($reviews)): ?>
		<div class="rating-box sell-infobox" >
			<div class="reviewslist">
				<?php
				foreach ($reviews as $review):

					/** @var Sellacious\Product $product */
					$product = $review->product;

					$url_raw = 'index.php?option=com_sellacious&view=product&p=' . $product->getCode();
					$url     = JRoute::_($url_raw);
					?>
					<div class="sell-row reviewtyped nomargin">

                        <div class="review-title">
                            <h3 class="pr-title"><?php echo $review->title ?></h3>
                        </div>
                        <div class="reviewauthor">
                            <div class="rating-stars rating-stars-md star-<?php echo $review->rating * 2 ?>">
                                <span class="starcounts"><?php echo number_format($review->rating, 1); ?></span>
                                <div class="author">
                                <h4 class="pr-author"><?php echo $review->author_name ?></h4>
                                <?php if ($review->buyer == 1): ?>
                                    <div class="buyer-badge"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_CERTIFIED_BUYER'); ?></div>
                                <?php endif; ?>
                                </div>
                            </div>
                            <div class="auth-det">

                                <h5 class="pr-date"><?php echo JHtml::_('date', $review->created, 'M d, Y'); ?></h5>
                            </div>
                        </div>

                        <p class="pr-body"><?php echo $review->comment ?></p>
					</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
		<?php endif; ?>
		<table class="w100p">
			<tr>
				<td class="text-center">
					<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
				</td>
			</tr>
			<tr>
				<td class="text-center">
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

		<?php
		if ($tmpl = $app->input->get('tmpl'))
		{
			?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
		}

		if ($layout = $app->input->get('layout'))
		{
			?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
		}

		echo JHtml::_('form.token');
		?>
</form>
