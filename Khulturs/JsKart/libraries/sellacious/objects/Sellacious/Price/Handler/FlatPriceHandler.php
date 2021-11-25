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
use stdClass;

defined('_JEXEC') or die;

/**
 * Handler class for flat pricing
 *
 * @since   2.0.0
 */
class FlatPriceHandler extends BasicPriceHandler
{
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

		$query->select('a.id AS price_id, a.seller_uid, a.cost_price, a.margin, a.margin_type')
		      ->select('a.list_price, a.calculated_price, a.is_fallback')
		      ->select('a.qty_min, a.qty_max, a.sdate, a.edate');

		$query->select('a.ovr_price, a.ovr_price AS product_price');
		$query->select('null AS client_catid');

		$query->from($db->qn('#__sellacious_product_prices', 'a'));

		$query->where('a.state = 1')
		      ->where('a.product_id = ' . (int) $productId)
		      ->where('a.seller_uid = ' . (int) $sellerUid)
		      ->where('a.is_fallback = 1');

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
