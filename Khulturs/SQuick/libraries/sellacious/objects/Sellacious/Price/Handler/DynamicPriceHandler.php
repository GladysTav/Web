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
use JFactory;
use Joomla\Utilities\ArrayHelper;
use SellaciousHelper;
use stdClass;

defined('_JEXEC') or die;

/**
 * Handler class for dynamic pricing
 *
 * @since   2.0.0
 */
class DynamicPriceHandler extends BasicPriceHandler
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
		$language = JFactory::getLanguage()->getTag();
		$now      = JFactory::getDate()->format('Y-m-d');
		$db       = JFactory::getDbo();
		$nullDt   = $db->getNullDate();
		$query    = $db->getQuery(true);

		$query->select('a.seller_uid, a.id AS price_id, a.cost_price, a.margin, a.margin_type')
		      ->select('a.list_price, a.calculated_price, a.ovr_price, a.product_price, a.is_fallback')
		      ->select('a.qty_min, a.qty_max, a.sdate, a.edate')
		      ->from($db->qn('#__sellacious_product_prices', 'a'))
		      ->where('a.product_id = ' . (int) $productId);

		if ($sellerUid)
		{
			$query->where('a.seller_uid = ' . (int) $sellerUid);
		}

		$query->where(sprintf('(((a.sdate <= %1$s OR a.sdate = %2$s) AND (a.edate >= %1$s OR a.edate = %2$s)) OR a.is_fallback = 1)', $db->q($now), $db->q($nullDt)))
		      ->where('a.state = 1');

		if ($quantity)
		{
			$query->where(sprintf('(a.qty_min <= %d OR a.qty_min = 0)', $quantity));
			$query->where(sprintf('(a.qty_max >= %d OR a.qty_max = 0)', $quantity));
		}

		$query->select('pcx.cat_id AS client_catid')
		      ->select('cc.title AS client_category')
		      ->join('LEFT', $db->qn('#__sellacious_productprices_clientcategory_xref', 'pcx') . ' ON pcx.product_price_id = a.id')
		      ->join('LEFT', $db->qn('#__sellacious_categories', 'cc') . ' ON cc.id = pcx.cat_id');

		/**
		 * If specified, the client category must match.
		 * If a price rule is set for no category (i.e. - implicitly all categories) we must take it too.
		 */
		if ($client_catid)
		{
			$query->where(sprintf('COALESCE(pcx.cat_id, 0) IN (%d, 0)', $client_catid));
		}

		$helper = SellaciousHelper::getInstance();
		$prices = $db->setQuery($query)->loadObjectList();

		list($markup, $percent) = $helper->client->getCategoryMarkup($client_catid);

		foreach ($prices as $iPrice)
		{
			$varPrice = null;

			if ($variantId)
			{
				$query = $db->getQuery(true);

				$query->select('vs.price_mod, vs.price_mod_perc')
				      ->from($db->qn('#__sellacious_variant_sellers', 'vs'))
				      ->where('vs.variant_id = ' . (int) $variantId)
				      ->where('vs.seller_uid = ' . (int) $iPrice->seller_uid);

				$varPrice = $db->setQuery($query)->loadObject();
			}

			if (empty($varPrice))
			{
				$varPrice = (object) array('price_mod' => 0, 'price_mod_perc' => 0);
			}

			// Todo: Verify if we need to convert seller currency before sorting @Mar 02, 2017@
			$iPrice->product_id     = $productId;
			$iPrice->variant_id     = $variantId;
			$iPrice->price_mod      = $varPrice->price_mod;
			$iPrice->price_mod_perc = $varPrice->price_mod_perc;
			$iPrice->variant_price  = $varPrice->price_mod_perc ? $iPrice->product_price * $varPrice->price_mod / 100.0 : $varPrice->price_mod;

			// Apply client category markup
			$totalAmount = $iPrice->product_price + $iPrice->variant_price;
			$totalAmount = $percent ? $totalAmount * (1 + $markup / 100.0) : $totalAmount + $markup;

			$iPrice->sales_price     = $totalAmount;
			$iPrice->basic_price     = $totalAmount;
			$iPrice->no_price        = abs($totalAmount) < 0.01;
			$iPrice->tax_amount      = 0.00;
			$iPrice->discount_amount = 0.00;

			$helper->translation->translateValue($iPrice->client_catid, 'sellacious_categories', 'title', $iPrice->client_category, $language);
		}

		$prices = ArrayHelper::sortObjects($prices, array('no_price', 'is_fallback', 'sales_price'));
		$items  = array();

		foreach ($prices as $price)
		{
			$hashKey = array(
				intval($price->qty_min),
				intval($price->qty_max),
				intval($price->client_catid),
			);
			$hash    = serialize($hashKey);

			if (!isset($items[$hash]))
			{
				$items[$hash] = $price;
			}
		}

		return $items;
	}

	/**
	 * Get the price records for given product-variant-seller
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $sellerUid  Seller user id
	 *
	 * @return  stdClass[]  The price records for the given PVS
	 *
	 * @since   2.0.0
	 */
	protected function getAllPrices($productId, $sellerUid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$now   = $db->q(JFactory::getDate()->toSql());
		$ndt   = $db->q($db->getNullDate());

		$query->select('a.id AS price_id, a.seller_uid, a.cost_price, a.margin, a.margin_type')
		      ->select('a.list_price, a.calculated_price, a.is_fallback')
		      ->select('a.qty_min, a.qty_max, a.sdate, a.edate');

		$query->select('a.ovr_price, a.product_price');
		$query->select('GROUP_CONCAT(cx.cat_id) AS client_catid');

		$query->from($db->qn('#__sellacious_product_prices', 'a'));
		$query->join('left', $db->qn('#__sellacious_productprices_clientcategory_xref', 'cx') . ' ON cx.product_price_id = a.id');

		$query->where('a.state = 1')
		      ->where('a.product_id = ' . (int) $productId)
		      ->where('a.seller_uid = ' . (int) $sellerUid)
		      ->where(sprintf('(((a.sdate <= %1$s OR a.sdate = %2$s) AND (a.edate >= %1$s OR a.edate = %2$s)) OR a.is_fallback = 1)', $now, $ndt));

		$query->group('a.id');

		return (array) $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Get the full layout name for a given sub-path
	 *
	 * @param   string  $layout  The layout file sub-path
	 * @param   string  $client  The active cms application name for the layout
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	protected function getLayout($layout, $client = null)
	{
		if (!$client)
		{
			try
			{
				$app    = JFactory::getApplication();
				$client = $app->getName();
			}
			catch (Exception $e)
			{
				$client = 'site';
			}
		}

		return sprintf('sellacious.pricing-type.%s.%s.%s', 'basic', $client, $layout);
	}
}
