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
use JLog;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;
use Sellacious\Product;
use SellaciousHelper;
use SellaciousTable;
use stdClass;

defined('_JEXEC') or die;

/**
 * Handler class for basic pricing
 *
 * @since   2.0.0
 */
class BasicPriceHandler extends AbstractPriceHandler
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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.seller_uid, a.id AS price_id, a.cost_price, a.margin, a.margin_type')
		      ->select('a.list_price, a.calculated_price, a.ovr_price, a.product_price, a.is_fallback')
		      ->select('a.qty_min, a.qty_max, a.sdate, a.edate')
		      ->from($db->qn('#__sellacious_product_prices', 'a'))
		      ->where('a.product_id = ' . (int) $productId);

		if ($sellerUid)
		{
			$query->where('a.seller_uid = ' . (int) $sellerUid);
		}

		$query->where('a.is_fallback = 1')
		      ->where('a.state = 1');

		$helper = SellaciousHelper::getInstance();
		$prices = $db->setQuery($query)->loadObjectList();

		list($markup, $percent) = $helper->client->getCategoryMarkup($client_catid);

		foreach ($prices as $iPrice)
		{
			$iPrice->price_mod      = 0;
			$iPrice->price_mod_perc = 0;
			$iPrice->variant_price  = 0;

			if ($variantId)
			{
				$query = $db->getQuery(true);

				$query->select('vs.price_mod, vs.price_mod_perc')
				      ->from($db->qn('#__sellacious_variant_sellers', 'vs'))
				      ->where('vs.variant_id = ' . (int) $variantId)
				      ->where('vs.seller_uid = ' . (int) $iPrice->seller_uid);

				$varPrice = $db->setQuery($query)->loadObject();

				if ($varPrice)
				{
					$iPrice->price_mod      = $varPrice->price_mod;
					$iPrice->price_mod_perc = $varPrice->price_mod_perc;
					$iPrice->variant_price  = $varPrice->price_mod_perc ? $iPrice->product_price * $varPrice->price_mod / 100.0 : $varPrice->price_mod;
				}
			}

			$totalAmount = $iPrice->product_price + $iPrice->variant_price;

			// Apply client category markup
			$totalAmount = $percent ? $totalAmount * (1 + $markup / 100.0) : $totalAmount + $markup;

			$iPrice->product_id      = $productId;
			$iPrice->variant_id      = $variantId;
			$iPrice->sales_price     = $totalAmount;
			$iPrice->basic_price     = $totalAmount;
			$iPrice->no_price        = abs($totalAmount) < 0.01;
			$iPrice->tax_amount      = 0.00;
			$iPrice->discount_amount = 0.00;
		}

		return $prices;
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
		$collection = array();

		foreach ($items as &$item)
		{
			$uid = (int) $item->get('seller_uid');

			if ($item->get('pricing_type') === $this->getName())
			{
				$collection[$uid][] = &$item;
			}
		}

		foreach ($collection as $uid => $records)
		{
			$this->processPricesForCache($productId, $uid, $records);
		}
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
		try
		{
			$helper = SellaciousHelper::getInstance();
			$catid  = $helper->client->getCategory(null, true);
			$prices = array();

			$item->advance_prices = json_decode($item->advance_prices) ?: array();

			foreach ($item->advance_prices as $k => &$price)
			{
				$cids = $price->client_catid ? explode(',', $price->client_catid) : array();

				// Skip prices not applicable for current user category
				if ($cids && !in_array($catid, $cids))
				{
					continue;
				}

				$price->product_id = $item->product_id;
				$price->variant_id = $item->variant_id;

				try
				{
					$helper->shopRule->toProduct($price, false, true);
				}
				catch (Exception $e)
				{
					// Ignore for now, and let continue processing without shoprule

					JLog::add($e->getMessage(), JLog::WARNING);
				}

				$prices[] = $price;
			}

			if ($prices)
			{
				$prices = ArrayHelper::sortObjects($prices, array('is_fallback', 'sales_price'));
				$price  = reset($prices);

				$item->list_price  = $price->list_price;
				$item->sales_price = $price->sales_price;

				$item->display_list_price  = $helper->currency->display($item->list_price, $item->seller_currency, '', true);
				$item->display_price       = $helper->currency->display($item->sales_price, $item->seller_currency, '', true);
			}

			$item->prices = $prices;
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Check whether the product price is valid according to this handler.
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
		try
		{
			$me      = JFactory::getUser();
			$helper  = SellaciousHelper::getInstance();
			$product = new Product($productId, $variantId);
			$catId   = $helper->client->getCategory($me->id, true);
			$price   = $product->getPrice($sellerUid, 1, $catId);

			return $price->sales_price >= 0.01;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the price records for given product-seller
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

		$query->select('a.id AS price_id, a.seller_uid, a.cost_price, a.margin, a.margin_type')
		      ->select('a.list_price, a.calculated_price, a.is_fallback')
		      ->select('a.qty_min, a.qty_max, a.sdate, a.edate');

		$query->select('a.ovr_price, a.product_price');
		$query->select('null AS client_catid');

		$query->from($db->qn('#__sellacious_product_prices', 'a'));

		$query->where('a.state = 1')
		      ->where('a.product_id = ' . (int) $productId)
		      ->where('a.seller_uid = ' . (int) $sellerUid)
		      ->where('a.is_fallback = 1');

		return (array) $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Get the price records for given product-variant-seller
	 *
	 * @param   int         $productId  Product id
	 * @param   int         $sellerUid  Seller user id
	 * @param   Registry[]  $items      The registry object with all attributes populated for cache record
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function processPricesForCache($productId, $sellerUid, array &$items)
	{
		$originals = array();
		$prices    = $this->getAllPrices($productId, $sellerUid);

		foreach ($items as &$item)
		{
			foreach ($prices as &$prc)
			{
				$mod    = $item->get('variant_price_mod');
				$vPrice = $mod ? ($item->get('variant_price_mod_perc') ? $prc->product_price * $mod / 100 : $mod) : 0;
				$sPrice = $prc->product_price + $vPrice;

				$prc->no_price    = abs($sPrice) < 0.01;
				$prc->basic_price = $sPrice;
				$prc->sales_price = $sPrice;
				$prc->product_id  = $productId;
				$prc->variant_id  = $item->get('variant_id');

				$originals[] = clone $prc;

				try
				{
					$helper = SellaciousHelper::getInstance();
					$helper->shopRule->toProduct($prc, false, true);
				}
				catch (Exception $e)
				{
					// Ignore for now
					JLog::add($e->getMessage(), JLog::WARNING);
				}
			}

			if (count($prices))
			{
				$prices = ArrayHelper::sortObjects($prices, array('is_fallback', 'sales_price'));
				$price  = reset($prices);

				$item->set('list_price', $price->list_price);
				$item->set('basic_price', $price->basic_price);
				$item->set('sales_price', $price->sales_price);
				$item->set('advance_prices', json_encode($originals));
			}
		}
	}

	/**
	 * Method to populate form data the product edit form
	 *
	 * @param   mixed  $data       The submitted product form data
	 * @param   int    $productId  The product id
	 * @param   int    $sellerUid  The seller user id
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function processFormData(&$data, $productId, $sellerUid)
	{
		if (empty($data->prices) && $productId)
		{
			$fallback_price = $this->getFormData($productId, $sellerUid, true);
			$product_prices = $this->getFormData($productId, $sellerUid, false);

			$data->prices           = new stdClass;
			$data->prices->fallback = reset($fallback_price);
			$data->prices->product  = $product_prices;

			try
			{
				$helper = SellaciousHelper::getInstance();
				$config = ConfigHelper::getInstance('com_sellacious');

				if ($config->get('multi_variant'))
				{
					$filter = array(
						'list.select' => 's.*',
						'list.join'   => array(
							array('left', '#__sellacious_variant_sellers AS s ON s.variant_id = a.id'),
						),
						'list.where'  => array(
							'a.product_id = ' . (int) $productId,
							's.seller_uid = ' . (int) $sellerUid,
						),
					);

					$data->prices->variants = $helper->variant->loadObjectList($filter);
				}
			}
			catch (Exception $e)
			{
				// Ignore
			}

		}
	}

	/**
	 * Get all price variations for a combination of a product and a seller
	 *
	 * @param   int   $productId  Product Id in concern
	 * @param   int   $sellerUid  Selected seller
	 * @param   bool  $fallback   Load the fallback price for selected product & seller
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function getFormData($productId, $sellerUid, $fallback = false)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$table = SellaciousTable::getInstance('ProductPrices');

		$query->select('a.*')
		      ->from($db->qn($table->getTableName(), 'a'))
		      ->where('a.product_id = ' . $db->q($productId))
		      ->where('a.seller_uid = ' . $db->q($sellerUid))
		      ->where('a.is_fallback = ' . $db->q($fallback ? 1 : 0))
		      ->select('GROUP_CONCAT(cat_id) AS cat_id')
		      ->join('left', $db->qn('#__sellacious_productprices_clientcategory_xref', 'ccx') . ' ON ccx.product_price_id = a.id')
		      ->group('a.id');

		$db->setQuery($query);

		$prices = $db->loadObjectList();

		if ($prices)
		{
			foreach ($prices as &$price)
			{
				$price->cat_id = json_decode('[' . $price->cat_id . ']');
			}
		}

		return (array) $prices;
	}
}
