<?php
/**
 * @version     2.0.0
 * @package     Sellacious.Product
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Media\Image\ResizeImage;
use Sellacious\Price\PriceHelper;
use Sellacious\Product;

$splCatId  = $params->get('splcategory');
$showBadge = count(array_intersect((array) $context, (array) $config->get('splcategory_badge_display')));
$products  = array();

foreach ($items as $item)
{
	/** @var  mixed  $item */
	$item->css_class = array();
	$item->spl_badge = null;

	$sCatId = null;

	// If a fixed standout is needed, it should set the relevant standout category id
	// If NO standout is needed, it should set boolean 'false' value
	if ($params->get('standout_special_category'))
	{
		if (is_numeric($splCatId))
		{
			$sCatId = $splCatId;
		}
		elseif (is_array($item->spl_category))
		{
			$sCatId = key($item->spl_category);
		}

		if ($sCatId)
		{
			$item->css_class[] = 'spl-cat-' . $sCatId;
		}
	}

	if ($showBadge && $sCatId)
	{
		$splCat       = $helper->splCategory->getItem($sCatId);
		$splCatParams = new Registry($splCat->params);
		$badgeOptions = $splCatParams->get('badge.options', 'icon');
		$badgeText    = $splCatParams->get('badge.text');
		$badge        = ImageHelper::getImage('splcategories', $sCatId, 'badge');

		if ($badgeOptions == 'icon' && $badge)
		{
			$item->spl_badge = $badge->getUrl();
		}
		elseif ($badgeOptions == 'text' && $badgeText)
		{
			$item->spl_badge_text       = $badgeText;
			$item->spl_badge_text_style = '';

			$color = $splCatParams->get('badge.styles.color');

			if ($color)
			{
				$item->spl_badge_text_style = 'color: ' . $color . ';';
			}
		}
	}

	$pObj   = new Product($item->product_id, $item->variant_id, $item->seller_uid);
	$images = $pObj->getProductImages();

	if (!$images)
	{
		$images[] = ImageHelper::getBlank('products', 'images');
	}

	foreach ($images as $image)
	{
		$item->orImages[] = $image->getUrl();
		$item->images[]   = $image->getResized(250, 250, 100, ResizeImage::RESIZE_EXACT_HEIGHT)->getUrl();
		$item->thumbs[]   = $image->getResized(50, 50, 100, ResizeImage::RESIZE_EXACT_HEIGHT)->getUrl();
	}

	$item->product_features = (array) $item->product_features;

	$item->url            = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $item->code, false);
	$item->b64text_mobile = $helper->media->writeText($item->seller_mobile, 2, true);
	$item->b64text_email  = $helper->media->writeText($item->seller_email, 2, true);
	$item->compare_allow  = $helper->product->isComparable($item->id);

	$item->rendered_attr = $helper->product->getRenderedAttributes($item->code, $item->product_id, $item->variant_id, $item->seller_uid);

	$priceHandler  = PriceHelper::getHandler($item->pricing_type);

	$priceHandler->renderVueLayout();

	if ($item->pricing_type == 'queryform')
	{
		JHtml::_('stylesheet', 'sellacious/product.price-queryform.css', array('relative' => true, 'version' => S_VERSION_CORE));
	}

	$stock  = $item->stock_capacity;
	$minQty = $item->quantity_min;

	$item->check_stock = $stock > 0 && (!$minQty || $stock >= $minQty);
	
	$item->checkout_form = $helper->cart->getCheckoutForm(false, 'products', $item->code);

	$products[] = $item;
}
