<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;
use Sellacious\Price\PriceHelper;
use Sellacious\Product;

/** @var  SellaciousViewProducts $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');

JHtml::_('stylesheet', 'com_sellacious/view.products.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.products.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/plugin/clipboardjs/clipboard.min.js', array('version' => S_VERSION_CORE, 'relative' => true));

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$ordering   = ($listOrder == 'a.ordering');
$saveOrder  = ($listOrder == 'a.ordering' && strtolower($listDirn) == 'asc');
$c_currency = $this->helper->currency->current('code_3');
$g_currency = $this->helper->currency->getGlobal('code_3');

$filter          = array('list.select' => 'a.id, a.title', 'list.where' => array('a.state = 1', 'a.level > 0'), 'list.order' => 'a.lft');
$splCategories   = $this->helper->splCategory->loadObjectList($filter);
$multi_seller    = $this->helper->config->get('multi_seller', 0);
$multi_variant   = $this->helper->config->get('multi_variant', 0);
$free_listing    = $this->helper->config->get('free_listing');
$allowDuplicates = $this->helper->config->get('allow_duplicate_products');
$canView         = $this->helper->access->checkAny(array('product.list', 'product.list.own'));

$stock_in_catalogue   = $this->helper->config->get('show_stock_in_catalogue', 1);
$ratings_in_catalogue = $this->helper->config->get('show_ratings_in_catalogue', 1);
$orders_in_catalogue  = $this->helper->config->get('show_orders_in_catalogue', 1);

$me    = JFactory::getUser();
$icons = array(
	'physical'   => 'fa fa-cube',
	'electronic' => 'fa fa-download',
	'package'    => 'fa fa-cubes',
);

foreach ($this->items as $i => $item)
{
	/** @var  mixed  $item */
	$isOwn = $item->owned_by == $me->id || $item->seller_uid == $me->id;
	$e4all = $this->helper->access->checkAny(array('basic', 'seller', 'shipping', 'related', 'seo'), 'product.edit.', $item->product_id);
	$e4own = $this->helper->access->checkAny(array('basic.own', 'seller.own', 'shipping.own', 'related.own', 'seo.own'), 'product.edit.', $item->product_id);
	$e4var = ($e4all || $e4own) && $this->helper->access->checkAny(array('create'), 'variant.');

	$canEdit   = $e4all || ($e4own && $isOwn);
	$canChange = $this->helper->access->check('product.edit.state', $item->product_id);

	$product   = new Product($item->product_id, null, $item->seller_uid);

	$images	   = $product->getProductImages();

	$thumb_urls = array();
	$big_urls   = array();

	if ($images)
	{
		foreach ($images as $image)
		{
			try
			{
				$thumb = $image->getResized(50, 50, 85, ResizeImage::RESIZE_FIT);
			}
			catch (Exception $e)
			{
				$thumb = $image;
			}
			array_push($thumb_urls, $thumb->getUrl());

			try
			{
				$big_image = $image->getResized(300, 300, 100, ResizeImage::RESIZE_EXACT_WIDTH);
			}
			catch (Exception $e)
			{
				$big_image = $image;
			}
			array_push($big_urls, $big_image->getUrl());
		}
	}
	else
	{
		$blank = ImageHelper::getBlank('com_sellacious/products', 'images');

		try
		{
			$thumb = $blank->getResized(50, 50, 85, ResizeImage::RESIZE_FIT);
		}
		catch (Exception $e)
		{
			$thumb = $blank;
		}
		array_push($thumb_urls, $thumb->getUrl());

		try
		{
			$big_image = $blank->getResized(300, 300, 100, ResizeImage::RESIZE_EXACT_WIDTH);
		}
		catch (Exception $e)
		{
			$big_image = $blank;
		}
		array_push($big_urls, $big_image->getUrl());
	}

	$edit_url  = JRoute::_('index.php?option=com_sellacious&task=product.edit&id=' . $item->product_id . ':' . $item->seller_uid);

	$language  = $this->helper->product->getLanguage($item->language);

	/** @note  JRoute::link() is not available until Joomla 3.9 or later */
	$site_url  = 'index.php?option=com_sellacious&view=product&p=' . $item->code;

	// Site route will be available if we could use JRoute::link, use 'isset' to test if we have it.
	if (is_callable(array('JRoute', 'link'))):
	// @fixme: B/C against J3.9
	// $siteRoute = call_user_func_array(array('JRoute', 'link'), array('site', $site_url));
	$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
	else:
		$site_url  = trim(JUri::root(), '/') . '/' . $site_url;
	endif;
	?>
	<tr role="row">
		<td class="nowrap center hidden-phone">
			<?php /* Any method using product_id:seller_uid can use this value. This is temporary workaround and should be improved */ ?>
			<label><input type="checkbox" name="cid[]" id="cb<?php echo $i ?>" class="checkbox style-0"
						  value="<?php echo $item->product_id ?>:<?php echo $item->seller_uid ?>" onclick="Joomla.isChecked(this.checked);"
					<?php echo ($canEdit || $canChange) ? '' : ' disabled="disabled"' ?>/>
				<span></span></label>
		</td>
		<td class="nowrap center">
			<span class="btn-round"><?php
				echo JHtml::_('products.status', $item->state, $i, $canChange); ?></span>
		</td>
		<td style="width:50px; padding:1px;" class="image-box" data-rollover="container">
			<span class="image-large bgrollover" style="background-image: url('<?php echo reset($big_urls); ?>')" data-rollover="<?php echo htmlspecialchars(json_encode($big_urls)); ?>"></span>
			<span class="image-small bgrollover" style="background-image: url('<?php echo reset($thumb_urls); ?>')" data-rollover="<?php echo htmlspecialchars(json_encode($thumb_urls)); ?>"></span>
		</td>
		<td class="product-editable">
			<span class="txt-color-red">&nbsp;<i class="<?php echo ArrayHelper::getValue($icons, $item->type, 'fa fa-cube') ?>"></i>&nbsp;</span>
			<?php echo ($canView && ($e4all || $e4own || $e4var)) ? JHtml::link($edit_url, $this->escape($item->title), array('title' => $this->escape($item->title), 'class' => 'hasTooltip')) : $this->escape($item->title); ?> (<?php echo $item->code; ?>)
			<span class="txt-color-red">&nbsp;<a target="_blank" class="hasTooltip" data-placement="right"
												 title="<?php echo JText::_('COM_SELLACIOUS_PRODUCT_LINK_FRONTEND_TIP'); ?>"
												 href="<?php echo isset($siteRoute) ? $siteRoute : $site_url; ?>"><i class="fa fa-external-link-square"></i></a>&nbsp;</span>
			<br/>
			<span>
				<?php
				if (!$allowDuplicates && isset($item->unique_field_name) && $item->unique_field_name && trim($item->unique_field_value)):
					echo $item->unique_field_title;
					echo JText::sprintf(': <b>%s</b>', $this->escape($item->unique_field_value));
					echo '<br>';
				elseif ($sku = trim($item->local_sku)):
					echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_SKU');
					echo JText::sprintf(': <b>%s</b>', $this->escape($sku));
					echo '<br>';
				endif;
				?>
				<?php echo JText::_('COM_SELLACIOUS_PRODUCT_HEADING_CATEGORY') ?>:
				<?php if ($pks = array_filter(explode(',', $item->category_ids))): ?>
					<?php
					try
					{
						$trees  = $this->helper->category->getTreeLevels($pks, true, 'b.title', $item->language);
						$states = $this->helper->category->getTreeLevels($pks, true, 'b.state');

						foreach ($trees as $key => $tree):
							$active      = array_pop($states[$key]);
							$activeClass = $active ? '' : 'text-inactive';
							?><label class="label capsule text-normal <?php echo $activeClass?>"><?php echo implode(' / ', $tree); ?> </label><wbr><?php
						endforeach;
					}
					catch (Exception $e)
					{
						foreach ($item->category_titles as $categoryTitle):
							?><label class="label capsule text-normal">.../ <?php echo $categoryTitle; ?> </label><wbr><?php
						endforeach;
					}
					?>
				<?php else: ?>
					<label class="label capsule text-normal label-info"><?php echo JText::_('COM_SELLACIOUS_PRODUCTS_CATEGORY_NONE') ?> </label>
				<?php endif; ?>
			</span>
		</td>

		<?php if ($multi_variant <> 0): ?>
		<td class="text-center nowrap">
			<?php if (!empty($item->variant_count)): ?>
				<span class="badge badge-sm badge-important hasTooltip" data-placement="right" style="margin-top: -2px;"
					  title="<?php echo JText::plural('COM_SELLACIOUS_PRODUCT_VARIANT_COUNT_TIP', $item->variant_count); ?>"><?php echo $item->variant_count ?></span>
			<?php endif; ?>
		</td>
		<?php endif; ?>

		<?php if (count($splCategories)): ?>
			<td class="text-center nowrap" style="width: 60px; white-space: nowrap;">
				<div class="btn-group btn-spl-listing" style="width: 70px; white-space: nowrap;">
					<?php
					foreach ($splCategories as $splCategory)
					{
						$this->helper->translation->translateRecord($splCategory, 'sellacious_splcategory');

						$active = in_array($splCategory->id, $item->spl_categories);
						$enable = $item->product_id && $splCategory->id && $item->seller_uid ? '' : ' disabled ';
						?>
						<button type="button" class="btn btn-xs hasTooltip <?php echo $active ? 'active btn-danger' : 'btn-default' ?>"
								title="<?php echo $this->escape($splCategory->title) ?>"
								data-id="<?php echo $item->product_id ?>"
								data-catid="<?php echo $splCategory->id ?>"
								data-seller_uid="<?php echo $item->seller_uid ?>" <?php echo $enable ?>><?php
						echo StringHelper::strtoupper(StringHelper::substr($splCategory->title, 0, 1)) ?></button><?php
					}
					?>
				</div>
			</td>
		<?php endif; ?>

		<td class="nowrap text-center">
			<?php
			$nullDt = JFactory::getDbo()->getNullDate();

			if ($item->listing_publish_up == $nullDt || empty($item->listing_publish_up))
			{
				echo JText::_('COM_SELLACIOUS_PRODUCT_LISTING_INACTIVE_LABEL');
			}
			else
			{
				echo JHtml::_('date', $item->listing_publish_up, 'M d, Y H:i');
			}
			?>
		</td>
		<?php if ($multi_seller && !$free_listing): ?>
			<td class="nowrap text-center">
				<?php
				$nullDt = JFactory::getDbo()->getNullDate();

				if ($item->listing_publish_down == $nullDt || empty($item->listing_publish_down))
				{
					echo JText::_('COM_SELLACIOUS_PRODUCT_LISTING_INACTIVE_LABEL');
				}
				elseif (strtotime($item->listing_publish_down) < strtotime('last midnight'))
				{
					echo JText::_('COM_SELLACIOUS_PRODUCT_LISTING_INACTIVE_LABEL');
				}
				else
				{
					echo JHtml::_('date', $item->listing_publish_down, 'M d, Y H:i');
				}
				?>
			</td>
		<?php endif; ?>
		<?php if ($stock_in_catalogue): ?>
		<td class="nowrap text-center">
			<?php
			list($allowP) = $this->helper->product->getStockHandling($item->product_id);
			list($allowS) = $this->helper->product->getStockHandling($item->product_id, $item->seller_uid);

			if ($allowP && !$allowS)
			{
				echo '<span>' . JText::_('COM_SELLACIOUS_PRODUCT_STOCK_DISABLED') . '</span>';
			}
			elseif ($item->stock_capacity > 0)
			{
				echo sprintf($item->over_stock ? '%d (%d + %d)' : '%d', $item->stock_capacity, $item->stock, $item->over_stock);
			}
			else
			{
				echo '<span class="red">' . JText::_('COM_SELLACIOUS_PRODUCT_OUT_OF_STOCK') . '</span>';
			}
			?>
		</td>
		<?php endif; ?>
		<?php
		$rate_url = JRoute::_('index.php?option=com_sellacious&view=ratings&filter[search]=' . $item->code);
		$rating   = $this->helper->rating->getProductRating($item->product_id);
		?>
		<?php if ($ratings_in_catalogue): ?>
		<td class="center">
			<a class="rating-stars" target="_blank" href="<?php echo $rate_url; ?>">
				<?php echo $rating ? $this->helper->core->getStars($rating->rating, true) : 'NA' ?>
			</a>
		</td>
		<?php endif; ?>
		<?php if ($orders_in_catalogue): ?>
		<td class="center">
			<?php echo $item->order_count ? sprintf('%d (%d)', $item->order_count, $item->order_units) : '&mdash;'; ?>
		</td>
		<?php endif; ?>

		<td class="nowrap text-center">
			<?php
			try
			{
				$handler = PriceHelper::getHandler($item->pricing_type ?: 'hidden');

				echo $handler->renderLayout('price.cached', $item);

				echo "<div><label class='label label-default'>{$handler->getTitle()}</label></div>";
			}
			catch (Exception $e)
			{
				echo '&mdash;';
			}
			?>
		</td>

		<?php if ($multi_seller): ?>
			<td class="nowrap">
				<span>
				<?php if ($item->seller_active): ?>
					<i class="fa fa-check txt-color-blue hasTooltip" title="<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SELLER_ACTIVE_TIP') ?>"></i>
				<?php else: ?>
					<i class="fa fa-times txt-color-orange hasTooltip" title="<?php echo JText::sprintf('COM_SELLACIOUS_PRODUCT_SELLER_INACTIVE_TIP') ?>"></i>
				<?php endif; ?>
				</span>
				<?php
				echo $this->escape($item->seller_store ?: $item->seller_name ?: $item->seller_company ?: $item->seller_username ?: $item->seller_uid);
				?>
			</td>
			<td>
				<span class="input-group">
					<span class="onoffswitch onoffswitch-selling">
						<input type="checkbox" class="onoffswitch-checkbox" id="st<?php echo $i ?>"
							<?php echo $item->is_selling ? ' checked ' : ' ' ?> <?php echo $item->seller_uid ? '' : ' disabled ' ?>
							   onclick="return listItemTask2('<?php echo $i ?>', 'products.<?php echo $item->is_selling ? 'setNotSelling' : 'setSelling' ?>', 'cb', this.form);">
						<label class="onoffswitch-label" for="st<?php echo $i ?>">
							<span class="onoffswitch-inner" data-swchon-text="Active" data-swchoff-text="Inactive"></span>
							<span class="onoffswitch-switch"></span>
						</label>
					</span>
				</span>
			</td>
		<?php endif; ?>
		<?php if (!empty($this->languages)): ?>
		<td class="center">
			<?php
			if (isset($item->language) && !empty($item->language))
			{
				echo $language[$item->language];
			}
			else
			{
				echo JText::_('COM_SELLACIOUS_OPTION_PRODUCT_LISTING_SELECT_LANGUAGE_ALL');
			}
			?>
		</td>
		<?php endif; ?>
		<td class="center nowrap">
			<?php $label = JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_BUY_NOW'); ?>
			<button type="button" class="btn btn-primary btn-xs btn-copy-code hasTooltip"
					data-text="[sellacious.cart.buy=<?php echo $item->code . ';btn btn-success;' . $label ?>]"
					title="<?php echo $label ?>"> <i class="fa fa-bolt"></i> </button>

			<?php $label = JText::_('COM_SELLACIOUS_PRODUCT_BUTTON_CHECKOUT_OPTION_ADD_TO_CART'); ?>
			<button type="button" class="btn btn-primary btn-xs btn-copy-code hasTooltip"
					data-text="[sellacious.cart.add=<?php echo $item->code . ';btn btn-success;' . $label ?>]"
					title="<?php echo $label ?>"> <i class="fa fa-shopping-cart"></i> </button>
		</td>
		<td class="center hidden-phone">
			<span><?php echo (int) $item->product_id; ?></span>
		</td>
	</tr>
	<?php
}