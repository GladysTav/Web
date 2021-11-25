<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Price\Handler;

use Exception;
use Joomla\Registry\Registry;
use stdClass;

defined('_JEXEC') or die;

/**
 * Handler class for hide pricing
 *
 * @since   2.0.0
 */
class HiddenPriceHandler extends AbstractPriceHandler
{
	/**
	 * Get the product price for given quantity and client id
	 *
	 * @param   int  $productId     The product id
	 * @param   int  $variantId     The variant id
	 * @param   int  $sellerUid     The seller user id
	 * @param   int  $quantity      The product quantity
	 * @param   int  $client_catid  The client category id
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getProductPrices($productId, $variantId, $sellerUid, $quantity = null, $client_catid = null)
	{
		$price = $this->getPriceObject($productId, $variantId, $sellerUid);

		return array($price);
	}

	/**
	 * Get the price records for given product-variant-seller that will be stored in the cache.
	 * The original prices without shoprules should be stored aside as well, it will be used in {@see  processCacheProduct()}
	 * Must also set: `list_price`, `basic_price`, `sales_price` properties. These will be used for sorting and filtering.
	 *
	 * @param   int         $productId  Product id
	 * @param   Registry[]  $items      The registry object with all attributes populated for cache record
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function setPricesForCache($productId, array &$items)
	{
		// Nada
	}

	/**
	 * Process the prices records after its loaded from cache. Should write the result to `prices` property.
	 * The source data should be already present in the cached record {@see  setPricesForCache()}.
	 *
	 * @param   stdClass  $item  The product record loaded from cache
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function processCacheProduct($item)
	{
		// Nada
	}

	/**
	 * Check whether the product price is valid according  to this handler
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $variantId  Variant id
	 * @param   int  $sellerUid  Seller user id
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isValidPrice($productId, $variantId, $sellerUid)
	{
		return true;
	}

	/**
	 * Method to return a dummy placeholder price object for this pricing type
	 *
	 * @param   $productId
	 * @param   $variantId
	 * @param   $sellerUid
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	protected function getPriceObject($productId, $variantId, $sellerUid)
	{
		$price = new stdClass;

		$price->product_id       = $productId;
		$price->variant_id       = $variantId;
		$price->seller_uid       = $sellerUid;
		$price->price_id         = 0;
		$price->cost_price       = 0;
		$price->margin           = 0;
		$price->margin_type      = 0;
		$price->list_price       = 0;
		$price->calculated_price = 0;
		$price->ovr_price        = 0;
		$price->product_price    = 0;
		$price->is_fallback      = 0;
		$price->qty_min          = 0;
		$price->qty_max          = 0;
		$price->sdate            = 0;
		$price->edate            = 0;
		$price->client_catid     = 0;
		$price->price_mod        = 0;
		$price->price_mod_perc   = 0;
		$price->variant_price    = 0;
		$price->sales_price      = 0;
		$price->basic_price      = 0;
		$price->no_price         = 1;
		$price->tax_amount       = 0;
		$price->discount_amount  = 0;

		return $price;
	}

	/**
	 * Method to match the given pricing type with pattern
	 *
	 * @param   string  $pricingType
	 *
	 * @return  bool|int
	 *
	 * @since   2.0.0
	 */
	public function matchPricingType($pricingType)
	{
		return parent::matchPricingType($pricingType) || in_array($pricingType, array('none', 'no'));
	}
}
