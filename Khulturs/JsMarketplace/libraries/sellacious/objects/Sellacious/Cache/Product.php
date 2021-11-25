<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Cache;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache;

/**
 * @package   Sellacious\Cache
 *
 * @since   1.7.0
 */
class Product extends Cache
{
	/**
	 * @var  int
	 *
	 * @since   1.7.0
	 */
	protected $productId;

	/**
	 * @var  \stdClass
	 *
	 * @since   1.7.0
	 */
	protected $product;

	/**
	 * @var  \stdClass
	 *
	 * @since   1.7.0
	 */
	protected $price;

	/**
	 * @var  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $sellers = array();

	/**
	 * @var  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $variants = array();

	/**
	 * @var  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $items = array();

	/**
	 * @var  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $prices;

	/**
	 * @var  \stdClass[]
	 *
	 * @since   1.7.0
	 */
	protected $specifications;

	/**
	 * Constructor.
	 *
	 * @param   int  $productId
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function __construct($productId)
	{
		$this->productId = $productId;

		parent::__construct();
	}

	/**
	 * Method to remove the cache records linked to the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function delete()
	{
		$query = $this->db->getQuery(true);

		$query->clear()->delete('#__sellacious_cache_products')->where('product_id = ' . (int) $this->productId);
		$this->db->setQuery($query)->execute();

		$query->clear()->delete('#__sellacious_cache_prices')->where('product_id = ' . (int) $this->productId);
		$this->db->setQuery($query)->execute();

		$query->clear()->delete('#__sellacious_cache_specifications')->where('x__product_id = ' . (int) $this->productId);
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Build the cache
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function build()
	{
		$this->loadProduct();

		if (!$this->product)
		{
			return;
		}

		$this->loadSellerList();

		$this->loadVariantList();

		$this->loadCategories();

		$this->loadTypeAttributes();

		$this->loadTypeAttributesSeller();

		$this->loadListing();

		$this->loadSpecialCategories();

		$this->loadDefaultPrice();

		$this->loadPrices();

		$this->loadAdvancedPrices();

		$this->loadSpecifications();

		foreach ($this->sellers as $seller)
		{
			$psx = (object) array_merge((array) $this->product, (array) $seller);

			$psx->code          = $this->helper->product->getCode($psx->product_id, 0, $psx->seller_uid);
			$psx->variant_price = 0;
			$psx->sales_price   = $this->getSalesPrice($psx);

			$this->db->insertObject('#__sellacious_cache_products', $psx);

			if (isset($this->prices[(int) $psx->seller_uid]))
			{
				foreach ($this->prices[(int) $psx->seller_uid] as $price)
				{
					if ($price->is_fallback)
					{
						$price->sales_price = $psx->sales_price;
					}

					$this->db->insertObject('#__sellacious_cache_prices', $price, 'id');
				}
			}

			foreach ($this->variants as $variant)
			{
				$psv = (object) array_merge((array) $psx, (array) $variant);

				$psv->code          = $this->helper->product->getCode($psv->product_id, $psv->variant_id, $psv->seller_uid);
				$psv->variant_price = $psv->variant_price_mod_perc ? $psv->product_price * $psv->variant_price_mod / 100.0 : $psv->variant_price_mod;
				$psv->sales_price   = $this->getSalesPrice($psv);

				$this->db->insertObject('#__sellacious_cache_products', $psv);
			}
		}

		foreach ($this->specifications as $specification)
		{
			$this->db->insertObject('#__sellacious_cache_specifications', $specification, 'x__id');
		}
	}

	/**
	 * Method to remove the cache records linked to the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadProduct()
	{
		$allowed       = $this->helper->config->get('allowed_product_type');
		$allow_package = $this->helper->config->get('allowed_product_package');

		$columns = array(
			'product_id'          => 'a.id',
			'product_title'       => 'a.title',
			'product_alias'       => 'a.alias',
			'product_type'        => 'a.type',
			'product_sku'         => 'a.local_sku',
			'manufacturer_sku'    => 'a.manufacturer_sku',
			'manufacturer_id'     => 'a.manufacturer_id',
			'product_features'    => 'a.features',
			'product_introtext'   => 'a.introtext',
			'product_description' => 'a.description',
			'product_active'      => 'a.state',
			'metakey'             => 'a.metakey',
			'metadesc'            => 'a.metadesc',
			'primary_video_url'   => 'a.primary_video_url',
			'product_location'    => 'a.location',
			'tags'                => 'a.tags',
			'owner_uid'           => 'a.owned_by',
			'language'            => 'a.language',
			'listing_purchased'   => 'a.created',
			'listing_start'       => 'a.created',
		);

		$allowed = $allowed == 'both' ? array('physical', 'electronic') : array($allowed);

		if ($allow_package)
		{
			$allowed[] = 'package';
		}

		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));

		$query->from($this->db->quoteName('#__sellacious_products', 'a'))
			->where('(a.state = 0 OR a.state = 1)')
			->where('a.id = ' . (int) $this->productId)
			->where('(a.type = ' . implode(' OR a.type = ', $this->db->quote($allowed)) . ')');

		$item = $this->db->setQuery($query)->loadObject();

		if ($item && $item->manufacturer_id)
		{
			$query = $this->db->getQuery(true);

			$query->select('u.name, u.username, u.email')
			      ->select('m.title m_company, m.category_id m_catid, m.code m_code');

			$query->from($this->db->quoteName('#__users', 'u'));
			$query->where('u.id = ' . (int) $item->manufacturer_id);

			$query->join('left', $this->db->quoteName('#__sellacious_manufacturers', 'm') . ' ON m.user_id = u.id');

			$user = $this->db->setQuery($query)->loadObject();

			if ($user)
			{
				$item->manufacturer_name     = $user->name;
				$item->manufacturer_username = $user->username;
				$item->manufacturer_email    = $user->email;
				$item->manufacturer_company  = $user->m_company;
				$item->manufacturer_catid    = $user->m_catid;
				$item->manufacturer_code     = $user->m_code;
			}
		}

		$this->product = $item;
	}

	/**
	 * Method to load the list of sellers for the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadSellerList()
	{
		$query   = $this->db->getQuery(true);
		$columns = array(
			'psx_id'            => 'psx.id',
			'seller_uid'        => 'psx.seller_uid',
			'is_selling'        => 'psx.state',
			'price_display'     => 'psx.price_display',
			'quantity_min'      => 'psx.quantity_min',
			'quantity_max'      => 'psx.quantity_max',
			'stock'             => 'psx.stock',
			'over_stock'        => 'psx.over_stock',
			'stock_reserved'    => 'psx.stock_reserved',
			'stock_sold'        => 'psx.stock_sold',
			'seller_name'       => 'u.name',
			'seller_username'   => 'u.username',
			'seller_email'      => 'u.email',
			'block'             => 'u.block',
			'seller_company'    => 's.title',
			'seller_catid'      => 's.category_id',
			'seller_code'       => 's.code',
			'seller_store'      => 's.store_name',
			'store_address'     => 's.store_address',
			'store_location'    => 's.store_location',
			'seller_commission' => 's.commission',
			'seller_active'     => 's.state',
			'seller_mobile'     => 'p.mobile',
			'seller_website'    => 'p.website',
		);

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
		$query->from($this->db->quoteName('#__sellacious_product_sellers', 'psx'));
		$query->where('psx.product_id = ' . (int) $this->productId);

		if (!$this->helper->config->get('multi_seller', 0))
		{
			$default_seller = $this->helper->config->get('default_seller', 0);

			$query->where('(psx.seller_uid = ' . (int) $default_seller . ' OR COALESCE(psx.seller_uid, 0) = 0)');
		}

		$query->join('inner', $this->db->quoteName('#__users', 'u') . ' ON u.id = psx.seller_uid');
		$query->join('left', $this->db->quoteName('#__sellacious_sellers', 's') . ' ON s.user_id = u.id');
		$query->join('left', $this->db->quoteName('#__sellacious_profiles', 'p') . ' ON p.user_id = u.id');
		$query->join('left', $this->db->quoteName('#__sellacious_manufacturers', 'm') . ' ON m.user_id = u.id');

		$items = $this->db->setQuery($query)->loadObjectList() ?: array();

		$this->product->seller_count = count($items);

		$g_currency = $this->helper->currency->getGlobal('code_3');

		foreach ($items as $index => $item)
		{
			$sUid = (int) $item->seller_uid;

			$item->seller_active = $item->seller_active && !$item->block;

			try
			{
				$item->seller_currency = $this->helper->currency->forSeller($sUid, 'code_3');
			}
			catch (\Exception $e)
			{
				$item->seller_currency = null;
			}

			try
			{
				$item->forex_rate = $this->helper->currency->getRate($item->seller_currency, $g_currency);
			}
			catch (\Exception $e)
			{
				$item->forex_rate = null;
			}

			unset($item->block);

			$this->sellers[$sUid] = $item;
		}
	}

	/**
	 * Method to load the list of variants for the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadVariantList()
	{
		if (!$this->helper->config->get('multi_variant'))
		{
			return;
		}

		$columns = array(
			'variant_id'             => 'v.id',
			'variant_title'          => 'v.title',
			'variant_alias'          => 'v.alias',
			'variant_sku'            => 'v.local_sku',
			'variant_description'    => 'v.description',
			'variant_features'       => 'v.features',
			'variant_active'         => 'v.state',
			'vsx_id'                 => 'vsx.id',
			'variant_price_mod'      => 'vsx.price_mod',
			'variant_price_mod_perc' => 'vsx.price_mod_perc',
			'stock'                  => 'vsx.stock',
			'over_stock'             => 'vsx.over_stock',
			'stock_reserved'         => 'vsx.stock_reserved',
			'stock_sold'             => 'vsx.stock_sold',
			'is_selling_variant'     => 'vsx.state',
		);

		$query = $this->db->getQuery(true);

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
		$query->select('vsx.seller_uid');
		$query->from($this->db->quoteName('#__sellacious_variants', 'v'))
		      ->where('v.product_id = ' . (int) $this->productId);

		$query->join('left', $this->db->quoteName('#__sellacious_variant_sellers', 'vsx') . ' ON vsx.variant_id = v.id');

		$variants = $this->db->setQuery($query)->loadObjectList() ?: array();

		$this->product->variant_count = count($variants);

		foreach ($variants as $index => $variant)
		{
			$vId = (int) $variant->variant_id;

			$this->variants[$vId] = $variant;
		}
	}

	/**
	 * Method to load the list of categories for the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadCategories()
	{
		$query = $this->db->getQuery(true);

		$query->select('c.id, c.title')
		      ->from($this->db->quoteName('#__sellacious_categories', 'c'))
		      ->order('c.lft');

		$query->join('inner', $this->db->quoteName('#__sellacious_product_categories', 'pc') . ' ON c.id = pc.category_id')
		      ->where('pc.product_id = ' . (int) $this->productId);

		$records = $this->db->setQuery($query)->loadObjectList() ?: array();

		$records = ArrayHelper::getColumn($records, 'title', 'id');
		$titles  = implode('|:|', array_values($records));
		$ids     = implode(',', array_keys($records));

		$this->product->category_ids    = $ids;
		$this->product->category_titles = $titles;

		if ($ids)
		{
			$c = $this->helper->category->getFields($ids, 'core', true);
			$v = $this->helper->category->getFields($ids, 'variant', true);

			$this->product->core_fields    = json_encode($c);
			$this->product->variant_fields = json_encode($v);
		}
	}

	/**
	 * Method to load the product type specific attributes for the active product
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadTypeAttributes()
	{
		if ($this->product->product_type == 'physical')
		{
			$query = $this->db->getQuery(true);

			$query->select('length, width, height, weight, vol_weight, whats_in_box');

			$query->from($this->db->quoteName('#__sellacious_product_physical'))
			      ->where('product_id = ' . (int) $this->productId);

			$attribs = $this->db->setQuery($query)->loadAssoc() ?: array();

			foreach ($attribs as $key => $value)
			{
				$this->product->$key = $value;
			}
		}
	}

	/**
	 * Method to load the product type specific attributes for the active product for each seller
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadTypeAttributesSeller()
	{
		$query = $this->db->getQuery(true);

		if ($this->product->product_type == 'electronic')
		{
			$query->select('delivery_mode, download_limit, download_period, preview_mode, preview_url');
			$query->from($this->db->quoteName('#__sellacious_eproduct_sellers'));
		}
		elseif ($this->product->product_type == 'physical')
		{
			// Todo: whats_in_box
			$query->select('listing_type, item_condition, flat_shipping, shipping_flat_fee, return_days, exchange_days');
			$query->from($this->db->quoteName('#__sellacious_physical_sellers'));
		}
		elseif ($this->product->product_type == 'package')
		{
			// Todo: whats_in_box
			$query->select('listing_type, item_condition, flat_shipping, shipping_flat_fee, return_days, exchange_days');
			$query->from($this->db->quoteName('#__sellacious_package_sellers'));
		}
		else
		{
			return;
		}

		foreach ($this->sellers as $sKey => $seller)
		{
			$query->clear(('where'))->where('psx_id = ' . (int) $seller->psx_id);

			$attribs = $this->db->setQuery($query)->loadAssoc() ?: array();

			foreach ($attribs as $key => $value)
			{
				$this->sellers[$sKey]->$key = $value;
			}
		}
	}

	/**
	 * Method to load the listing attributes for the active product and each of its sellers
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadListing()
	{
		$free = $this->helper->config->get('free_listing');

		if ($free)
		{
			$date = \JFactory::getDate()->modify('+1 year');

			foreach ($this->sellers as &$seller)
			{
				$seller->listing_active = 1;
				$seller->listing_end    = $date->format('Y-12-31 23:59:59');
			}
		}
		else
		{
			$query  = $this->db->getQuery(true);
			$now    = \JFactory::getDate()->toSql();
			$nullDt = $this->db->getNullDate();

			$cols = array();

			$cols['listing_active']    = 'l.state';
			$cols['listing_purchased'] = 'l.subscription_date';
			$cols['listing_start']     = 'l.publish_up';
			$cols['listing_end']       = 'l.publish_down';

			foreach ($this->sellers as &$seller)
			{
				$query->select($this->db->quoteName(array_values($cols), array_keys($cols)));
				$query->from($this->db->quoteName('#__sellacious_seller_listing', 'l'));
				$query->where(array(
					'l.product_id = ' . (int) $this->productId,
					'l.seller_uid = ' . (int) $seller->seller_uid,
					'l.publish_up != ' . $this->db->q($nullDt),
					'l.publish_down != ' . $this->db->q($nullDt),
					'l.publish_up <= ' . $this->db->q($now),
					'l.publish_down > ' . $this->db->q($now),
					'l.category_id = 0',
					'l.state = 1',
				));

				$listing = $this->db->setQuery($query)->loadObject();

				if ($listing)
				{
					$seller->listing_active    = $listing->listing_active;
					$seller->listing_purchased = $listing->listing_purchased;
					$seller->listing_start     = $listing->listing_start;
					$seller->listing_end       = $listing->listing_end;
				}
				else
				{
					$seller->listing_active    = 0;
					$seller->listing_purchased = null;
					$seller->listing_start     = null;
					$seller->listing_end       = null;
				}
			}
		}
	}

	/**
	 * Method to load the special categories associated with the active product and each of its sellers
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadSpecialCategories()
	{
		$nullDt = $this->db->getNullDate();
		$now    = \JFactory::getDate()->toSql();

		$conditions = array(
			'l.category_id = c.id',
			'l.category_id > 0',
			'l.publish_up != ' . $this->db->q($nullDt),
			'l.publish_down != ' . $this->db->q($nullDt),
			'l.publish_up <= ' . $this->db->q($now),
			'l.publish_down > ' . $this->db->q($now),
			'l.state = 1',
		);

		foreach ($this->sellers as &$seller)
		{
			$query = $this->db->getQuery(true);

			$query->select('c.id, c.title')
			      ->from($this->db->quoteName('#__sellacious_splcategories', 'c'))
				  ->order('c.lft');

			$query->join('inner', $this->db->quoteName('#__sellacious_seller_listing', 'l') . ' ON ' . implode(' AND ', $conditions))
				->where('l.seller_uid = ' . (int) $seller->seller_uid)
				->where('l.product_id = ' . (int) $this->productId);

			$records = $this->db->setQuery($query)->loadObjectList() ?: array();

			$records = ArrayHelper::getColumn($records, 'title', 'id');
			$titles  = implode('|:|', array_values($records));
			$ids     = implode(',', array_keys($records));

			$seller->spl_category_ids    = $ids;
			$seller->spl_category_titles = $titles;
		}
	}

	/**
	 * Method to load the default price for the active product and each of its sellers
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadDefaultPrice()
	{
		$pricing_model = $this->helper->config->get('pricing_model');

		$query = $this->db->getQuery(true);
		$col   = $pricing_model == 'flat' ? 'p.ovr_price' : 'p.product_price';

		foreach ($this->sellers as &$seller)
		{
			$query->clear()
				  ->select('p.*')
			      ->select($this->db->quoteName($col, 'default_price'));

			$query->from($this->db->quoteName('#__sellacious_product_prices', 'p'));
			$query->where('p.product_id = ' . (int) $this->productId);
			$query->where('p.seller_uid = ' . (int) $seller->seller_uid);
			$query->where('p.is_fallback = 1');

			$this->price = $this->db->setQuery($query)->loadObject();

			$seller->product_price = $this->price ? $this->price->default_price : null;
			$seller->multi_price   = 0;
		}
	}

	/**
	 * Method to load the count of advance prices for the active product and each of its sellers
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadAdvancedPrices()
	{
		$pricing_model = $this->helper->config->get('pricing_model');

		if ($pricing_model == 'advance')
		{
			foreach ($this->sellers as &$seller)
			{
				$query = $this->db->getQuery(true);

				$query->select('COUNT(p.id) multi_price')
				      ->from($this->db->quoteName('#__sellacious_product_prices', 'p'))
				      ->where('p.is_fallback = 0')
				      ->where('p.state = 1')
				      ->where('p.product_id = ' . (int) $this->productId)
				      ->where('p.seller_uid = ' . (int) $seller->seller_uid);

				$seller->multi_price = $this->db->setQuery($query)->loadResult();
			}
		}
	}

	/**
	 * Method to load the prices for the active product and each of its sellers
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadPrices()
	{
		$query = $this->db->getQuery(true);

		$columns = array(
			'price_id'         => 'pp.id',
			'product_id'       => 'pp.product_id',
			'seller_uid'       => 'pp.seller_uid',
			'qty_min'          => 'pp.qty_min',
			'qty_max'          => 'pp.qty_max',
			'cost_price'       => 'pp.cost_price',
			'margin'           => 'pp.margin',
			'margin_type'      => 'pp.margin_type',
			'list_price'       => 'pp.list_price',
			'calculated_price' => 'pp.calculated_price',
			'ovr_price'        => 'pp.ovr_price',
			'product_price'    => 'pp.product_price',
			'is_fallback'      => 'pp.is_fallback',
			'sdate'            => 'pp.sdate',
			'edate'            => 'pp.edate',
			'client_catid'     => 'pcx.cat_id',
		);

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
		$query->from($this->db->quoteName('#__sellacious_product_prices', 'pp'))->where('pp.state = 1');
		$query->join('left', $this->db->quoteName('#__sellacious_productprices_clientcategory_xref', 'pcx') . ' ON pcx.product_price_id = pp.id');

		foreach ($this->sellers as &$seller)
		{
			$query->clear(('where'))
			      ->where('pp.seller_uid = ' . (int) $seller->seller_uid)
				  ->where('pp.product_id = ' . (int) $this->productId);

			$prices = $this->db->setQuery($query)->loadObjectList() ?: array();

			foreach ($prices as $price)
			{
				$price->currency    = $seller->seller_currency;
				$price->sales_price = null;

				$this->prices[(int) $seller->seller_uid][] = $price;
			}
		}
	}

	/**
	 * Method to load the specifications for the active product and each of its variants
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function loadSpecifications()
	{
		$records  = array();
		$columns  = $this->db->getTableColumns('#__sellacious_cache_specifications');
		$filter   = array(
			'list.from'  => '#__sellacious_field_values',
			'table_name' => 'products',
			'record_id'  => (int) $this->productId,
		);
		$iterator = $this->helper->field->getIterator($filter);

		$recordP = new \stdClass;

		$recordP->id            = null;
		$recordP->x__product_id = (int) $this->productId;
		$recordP->x__variant_id = 0;
		$recordP->x__state      = 1;

		foreach ($iterator as $obj)
		{
			$colName = 'spec_' . (int) $obj->field_id;

			if (array_key_exists($colName, $columns))
			{
				$recordP->$colName = $obj->is_json ? 'JSON:' . $obj->field_value : $obj->field_value;
			}
		}

		$records[] = $recordP;

		foreach ($this->variants as $variant)
		{
			$filter   = array(
				'list.from'  => '#__sellacious_field_values',
				'table_name' => 'variants',
				'record_id'  => (int) $variant->variant_id,
			);
			$iterator = $this->helper->field->getIterator($filter);

			$recordV = clone $recordP;

			$recordV->x__variant_id = (int) $variant->variant_id;

			foreach ($iterator as $obj)
			{
				$colName = 'spec_' . (int) $obj->field_id;

				if (array_key_exists($colName, $columns))
				{
					$recordV->$colName = $obj->is_json ? 'JSON:' . $obj->field_value : $obj->field_value;
				}
			}

			$records[] = $recordV;
		}

		$this->specifications = $records;
	}

	/**
	 * Method to remove the cache records linked to the active product
	 *
	 * @param   \stdClass  Product-seller connection record (aka, PSX. Everywhere)
	 *
	 * @return  float
	 *
	 * @since   1.7.0
	 */
	protected function getSalesPrice($psv)
	{
		if (!$this->price)
		{
			return null;
		}

		$price = clone $this->price;

		$price->variant_id  = $psv->variant_id;
		$price->basic_price = $psv->product_price + $psv->variant_price;

		try
		{
			$this->helper->shopRule->toProduct($price, false, true);

			return $price->sales_price;
		}
		catch (\Exception $e)
		{
			// Ignore
			return null;
		}
	}
}
