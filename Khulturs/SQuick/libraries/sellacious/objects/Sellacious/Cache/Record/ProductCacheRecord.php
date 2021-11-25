<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
namespace Sellacious\Cache\Record;

defined('_JEXEC') or die;

use Exception;
use JDatabaseDriver;
use JFactory;
use JLog;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Event\EventHelper;
use Sellacious\Price\PriceHelper;
use SellaciousHelper;
use stdClass;

/**
 * @package   Sellacious\Cache
 *
 * @since   2.0.0
 */
class ProductCacheRecord
{
	/**
	 * The table columns for this record type
	 *
	 * @var   string[]
	 *
	 * @since   2.0.0
	 */
	protected static $columns = array();

	/**
	 * Sellacious application helper
	 *
	 * @var    SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * The database driver object
	 *
	 * @var    JDatabaseDriver
	 *
	 * @since   2.0.0
	 */
	protected $db;

	/**
	 * @var  int
	 *
	 * @since   2.0.0
	 */
	protected $productId;

	/**
	 * @var  stdClass
	 *
	 * @since   2.0.0
	 */
	protected $product;

	/**
	 * @var  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected $sellers = array();

	/**
	 * @var  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected $variants = array();

	/**
	 * Prices records for products / sellers
	 *
	 * @var  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected $prices;

	/**
	 * @var  Registry[]
	 *
	 * @since   2.0.0
	 */
	protected $items = array();

	/**
	 * Constructor
	 *
	 * @param   int  $productId
	 *
	 * @since   2.0.0
	 */
	public function __construct($productId)
	{
		try
		{
			$this->productId = $productId;
			$this->db        = JFactory::getDbo();
			$this->helper    = SellaciousHelper::getInstance();
		}
		catch (Exception $e)
		{
			// Not happening
		}
	}

	/**
	 * Get the column names for the cache table
	 *
	 * @return  string[]
	 *
	 * @since   2.0.0
	 */
	public static function getColumns()
	{
		if (static::$columns)
		{
			return static::$columns;
		}

		$product = array(
			'product_id',
			'product_title',
			'product_alias',
			'product_type',
			'product_sku',
			'local_sku',
			'manufacturer_sku',
			'manufacturer_id',
			'product_features',
			'product_introtext',
			'product_description',
			'product_active',
			'product_ordering',
			'metakey',
			'metadesc',
			'primary_video_url',
			'tags',
			'owner_uid',
			'language',
			'listing_purchased',
			'listing_start',
			'category',
			'category_ext',
			'length',
			'width',
			'height',
			'weight',
			'vol_weight',
			'shipping_length',
			'shipping_width',
			'shipping_height',
			'shipping_weight',
			'whats_in_box',
			'unique_field_name',
			'unique_field_title',
			'unique_field_value',
		);

		$mfr = array(
			'manufacturer_name',
			'manufacturer_company',
			'manufacturer_catid',
			'manufacturer_code',
		);

		$seller = array(
			'psx_id',
			'seller_uid',
			'seller_sku',
			'is_selling',
			'pricing_type',
			'quantity_min',
			'quantity_max',
			'disable_stock',
			'stock',
			'over_stock',
			'stock_reserved',
			'stock_sold',
			'seller_name',
			'seller_username',
			'seller_email',
			'seller_block',
			'seller_company',
			'seller_catid',
			'seller_code',
			'seller_store',
			'store_address',
			'seller_commission',
			'seller_active',
			'seller_mobile',
			'seller_website',
			'seller_currency',
			'forex_rate',
			'listing_type',
			'item_condition',
			'flat_shipping',
			'shipping_flat_fee',
			'return_days',
			'return_tnc',
			'exchange_days',
			'exchange_tnc',
			'whats_in_box',
			'delivery_mode',
			'download_limit',
			'download_period',
			'preview_mode',
			'preview_url',
			'listing_active',
			'listing_purchased',
			'listing_start',
			'listing_end',
			'spl_category',
			'seller_gl_id',
			'store_lat',
			'store_lng',
			'product_lat',
			'product_lng',
			'psx_country',
			'psx_state',
			'psx_district',
			'psx_city',
			'psx_locality',
			'psx_sublocality',
			'psx_zip',
			'store_country',
			'store_state',
			'store_district',
			'store_city',
			'store_locality',
			'store_sublocality',
			'store_zip',
		);

		$variant = array(
			'variant_id',
			'variant_title',
			'variant_alias',
			'variant_sku',
			'variant_description',
			'variant_features',
			'variant_active',
		);

		$vsx = array(
			'vsx_id',
			'variant_price_mod',
			'variant_price_mod_perc',
			'stock',
			'over_stock',
			'stock_reserved',
			'stock_sold',
			'is_selling_variant',
		);

		$ext = array(
			'code',
			'seller_count',
			'variant_count',
			'stock_capacity',
			'specifications',
			'product_rating',
			'order_count',
			'order_units',
			'is_visible',
			'related_products',
		);

		$price = array(
			'product_price',
			'variant_price',
			'list_price',
			'basic_price',
			'sales_price',
			'advance_prices',
		);

		static::$columns = array_merge($product, $mfr, $seller, $variant, $vsx, $ext, $price);

		return static::$columns;
	}

	/**
	 * Load the data for all variants and sellers for this product to be stored in the cache
	 *
	 * @return  Registry[]  List of cache records for this product
	 *
	 * @since   2.0.0
	 */
	public function getRecords()
	{
		$this->loadProduct();

		if ($this->product)
		{
			$this->loadSellerList();

			$this->loadVariantList();

			$this->loadCategories();

			$this->loadUniqueFields();

			$this->loadTypeAttributes();

			$this->loadTypeAttributesSeller();

			$this->loadListing();

			$this->loadSpecialCategories();
		}

		$this->batchProcess();

		return $this->items;
	}

	/**
	 * Method to load the product record for the active product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadProduct()
	{
		$allowed       = $this->helper->config->get('allowed_product_type');
		$allow_package = $this->helper->config->get('allowed_product_package');
		$multi_seller  = $this->helper->config->get('multi_seller', 0);

		$columns = array(
			'product_id'          => 'a.id',
			'product_title'       => 'a.title',
			'product_alias'       => 'a.alias',
			'product_type'        => 'a.type',
			'local_sku'           => 'a.local_sku',
			'manufacturer_sku'    => 'a.manufacturer_sku',
			'manufacturer_id'     => 'a.manufacturer_id',
			'product_features'    => 'a.features',
			'product_introtext'   => 'a.introtext',
			'product_description' => 'a.description',
			'product_active'      => 'a.state',
			'product_ordering'    => 'a.ordering',
			'metakey'             => 'a.metakey',
			'metadesc'            => 'a.metadesc',
			'primary_video_url'   => 'a.primary_video_url',
			'tags'                => 'a.tags',
			'owner_uid'           => 'a.owned_by',
			'language'            => 'a.language',
			'listing_purchased'   => 'a.created',
			'listing_start'       => 'a.created',
		);

		if (!$multi_seller)
		{
			$columns['product_sku'] = 'a.local_sku';
		}

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
			$user = $this->getManufacturer($item->manufacturer_id);

			if ($user)
			{
				$item->manufacturer_name    = $user->name;
				$item->manufacturer_company = $user->m_company;
				$item->manufacturer_catid   = $user->m_catid;
				$item->manufacturer_code    = $user->m_code;
			}
		}

		$this->product = $item;
	}

	/**
	 * Method to load the list of sellers for the active product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadSellerList()
	{
		$multiSeller = $this->helper->config->get('multi_seller', 0);

		$query   = $this->db->getQuery(true);
		$columns = array(
			'psx_id'            => 'psx.id',
			'seller_uid'        => 'psx.seller_uid',
			'seller_sku'        => 'psx.seller_sku',
			'is_selling'        => 'psx.state',
			'pricing_type'      => 'psx.pricing_type',
			'quantity_min'      => 'psx.quantity_min',
			'quantity_max'      => 'psx.quantity_max',
			'disable_stock'     => 'psx.disable_stock',
			'stock'             => 'psx.stock',
			'over_stock'        => 'psx.over_stock',
			'stock_reserved'    => 'psx.stock_reserved',
			'stock_sold'        => 'psx.stock_sold',
			'product_location'  => 'psx.product_location',
			'psx_country'       => 'psx.loc_country',
			'psx_state'         => 'psx.loc_state',
			'psx_district'      => 'psx.loc_district',
			'psx_city'          => 'psx.loc_city',
			'psx_locality'      => 'psx.loc_locality',
			'psx_sublocality'   => 'psx.loc_sublocality',
			'psx_zip'           => 'psx.loc_zip',
			'seller_name'       => 'u.name',
			'seller_username'   => 'u.username',
			'seller_email'      => 'u.email',
			'seller_block'      => 'u.block',
			'seller_company'    => 's.title',
			'seller_catid'      => 's.category_id',
			'seller_code'       => 's.code',
			'seller_store'      => 's.store_name',
			'store_address'     => 's.store_address',
			'store_location'    => 's.store_location',
			'store_country'     => 's.loc_country',
			'store_state'       => 's.loc_state',
			'store_district'    => 's.loc_district',
			'store_city'        => 's.loc_city',
			'store_locality'    => 's.loc_locality',
			'store_sublocality' => 's.loc_sublocality',
			'store_zip'         => 's.loc_zip',
			'seller_commission' => 's.commission',
			'seller_active'     => 's.state',
			'seller_mobile'     => 'p.mobile',
			'seller_website'    => 'p.website',
		);

		if ($multiSeller)
		{
			$columns['product_sku'] = 'psx.seller_sku';
		}

		$query->select($this->db->quoteName(array_values($columns), array_keys($columns)));
		$query->from($this->db->quoteName('#__sellacious_product_sellers', 'psx'));
		$query->where('psx.product_id = ' . (int) $this->productId);

		if (!$multiSeller)
		{
			$default_seller = $this->helper->config->get('default_seller', 0);

			$query->where('(psx.seller_uid = ' . (int) $default_seller . ' OR COALESCE(psx.seller_uid, 0) = 0)');
		}

		$query->join('inner', $this->db->quoteName('#__users', 'u') . ' ON u.id = psx.seller_uid');
		$query->join('left', $this->db->quoteName('#__sellacious_sellers', 's') . ' ON s.user_id = u.id');
		$query->join('left', $this->db->quoteName('#__sellacious_profiles', 'p') . ' ON p.user_id = u.id');

		$rows = $this->db->setQuery($query)->loadObjectList() ?: array();

		$this->product->seller_count = count($rows);

		$g_currency = $this->helper->currency->getGlobal('code_3');

		foreach ($rows as $index => $item)
		{
			$uid = (int) $item->seller_uid;

			$item->seller_active   = $item->seller_active && !$item->seller_block;
			$item->seller_currency = $this->helper->currency->forSeller($uid, 'code_3');

			// If no filter, add root node
			$locations = array('all' => $this->helper->seller->getShipLocations($uid) ?: array(1));

			$item->seller_gl_id = json_encode($locations);

			try
			{
				$item->forex_rate = $this->helper->currency->getRate($item->seller_currency, $g_currency);
			}
			catch (Exception $e)
			{
				$item->forex_rate = null;
			}

			$this->sellers[$uid] = $item;
		}
	}

	/**
	 * Method to load the list of variants for the active product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadVariantList()
	{
		if ($this->helper->config->get('multi_variant'))
		{
			$columns = array(
				'variant_id'          => 'v.id',
				'variant_title'       => 'v.title',
				'variant_alias'       => 'v.alias',
				'variant_sku'         => 'v.local_sku',
				'variant_description' => 'v.description',
				'variant_features'    => 'v.features',
				'variant_active'      => 'v.state',
			);

			$query = $this->db->getQuery(true);

			$query->select($this->db->quoteName(array_values($columns), array_keys($columns)))
				  ->from($this->db->quoteName('#__sellacious_variants', 'v'))
				  ->where('v.product_id = ' . (int) $this->productId);

			$this->variants = $this->db->setQuery($query)->loadObjectList('variant_id') ?: array();

			$this->product->variant_count = count($this->variants);
		}
	}

	/**
	 * Method to load the list of categories for the active product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadCategories()
	{
		$filter  = array(
			'list.select' => 'a.id, a.title',
			'list.join'   => array(
				array('inner', $this->db->quoteName('#__sellacious_product_categories', 'pc') . ' ON a.id = pc.category_id'),
			),
			'list.where'  => 'pc.product_id = ' . (int) $this->productId,
			'state'       => 1,
		);
		$records = $this->helper->category->loadObjectList($filter) ?: array();
		$records = ArrayHelper::getColumn($records, 'title', 'id');

		if ($records)
		{
			$aks = $this->helper->category->getParents(array_keys($records), true, array('state' => 1));
			$aks = $this->helper->category->loadObjectList(array('list.select' => 'a.id, a.title', 'id' => $aks));

			$values = ArrayHelper::getColumn($aks, 'title', 'id');

			$this->product->category     = json_encode($records);
			$this->product->category_ext = json_encode($values);
		}
	}

	/**
	 * Method to load unique field and its values for the product
	 *
	 * @since   2.0.0
	 */
	protected function loadUniqueFields()
	{
		$allowDuplicates = $this->helper->config->get('allow_duplicate_products');

		if (!$allowDuplicates && $this->product)
		{
			$categoryIds    = isset($this->product->category) ? array_keys(json_decode($this->product->category, true)) : array();
			$uniqueSetting  = $this->helper->product->getUniqueFieldSetting($categoryIds, false);
			$uniqueField    = $uniqueSetting->get('product_unique_field', '');
			$sellerUid      = isset($this->product->seller_uid) ? $this->product->seller_uid : 0;
			$productId      = isset($this->product->product_id) ? $this->product->product_id : 0;
			$uniqueFieldVal = $this->helper->product->getUniqueFieldValue($uniqueField, $productId, $sellerUid);

			$this->product->unique_field_name  = $uniqueField;
			$this->product->unique_field_title = $uniqueSetting->get('product_unique_field_title');
			$this->product->unique_field_value = $uniqueFieldVal;
		}
	}

	/**
	 * Method to load the product type specific attributes for the active product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadTypeAttributes()
	{
		if ($this->product->product_type == 'physical')
		{
			$query = $this->db->getQuery(true);

			$query->select('length, width, height, weight, vol_weight')
				  ->from($this->db->quoteName('#__sellacious_product_physical'))
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
	 * @since   2.0.0
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
			$query->select('listing_type, item_condition, flat_shipping, shipping_flat_fee, return_days, return_tnc, exchange_days, exchange_tnc, whats_in_box');
			$query->select('length as shipping_length, width as shipping_width, height as shipping_height, weight as shipping_weight');
			$query->from($this->db->quoteName('#__sellacious_physical_sellers'));
		}
		elseif ($this->product->product_type == 'package')
		{
			$query->select('listing_type, item_condition, flat_shipping, shipping_flat_fee, return_days, return_tnc, exchange_days, exchange_tnc, whats_in_box');
			$query->select('length as shipping_length, width as shipping_width, height as shipping_height, weight as shipping_weight');
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
	 * @since   2.0.0
	 */
	protected function loadListing()
	{
		$free = $this->helper->config->get('free_listing');

		if ($free)
		{
			$date = JFactory::getDate()->modify('+1 year');

			foreach ($this->sellers as $seller)
			{
				$seller->listing_active = 1;
				$seller->listing_end    = $date->format('Y-12-31 23:59:59');
			}
		}
		else
		{
			$query  = $this->db->getQuery(true);
			$now    = JFactory::getDate()->toSql();
			$nullDt = $this->db->getNullDate();

			$cols = array();

			$cols['listing_active']    = 'l.state';
			$cols['listing_purchased'] = 'l.subscription_date';
			$cols['listing_start']     = 'l.publish_up';
			$cols['listing_end']       = 'l.publish_down';

			foreach ($this->sellers as $seller)
			{
				$cond = array(
					'l.product_id = ' . (int) $this->productId,
					'l.seller_uid = ' . (int) $seller->seller_uid,
					'l.publish_up != ' . $this->db->q($nullDt),
					'l.publish_down != ' . $this->db->q($nullDt),
					'l.publish_up <= ' . $this->db->q($now),
					'l.publish_down > ' . $this->db->q($now),
					'l.category_id = 0',
					'l.state = 1',
				);

				$query->select($this->db->quoteName(array_values($cols), array_keys($cols)))
					  ->from($this->db->quoteName('#__sellacious_seller_listing', 'l'))
					  ->where($cond);

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
	 * @since   2.0.0
	 */
	protected function loadSpecialCategories()
	{
		$nullDt = $this->db->getNullDate();
		$now    = JFactory::getDate()->toSql();

		$conditions = array(
			'l.category_id = c.id',
			'l.category_id > 0',
			'l.publish_up != ' . $this->db->q($nullDt),
			'l.publish_down != ' . $this->db->q($nullDt),
			'l.publish_up <= ' . $this->db->q($now),
			'l.publish_down > ' . $this->db->q($now),
			'l.state = 1',
		);

		foreach ($this->sellers as $seller)
		{
			$query = $this->db->getQuery(true);

			$query->select('c.id, c.title')
				  ->from($this->db->quoteName('#__sellacious_splcategories', 'c'))
				  ->order('c.lft');

			$query->join('inner', $this->db->quoteName('#__sellacious_seller_listing', 'l') . ' ON ' . implode(' AND ', $conditions))
				  ->where('l.seller_uid = ' . (int) $seller->seller_uid)
				  ->where('l.product_id = ' . (int) $this->productId);

			$records = $this->db->setQuery($query)->loadObjectList() ?: array();

			if ($records)
			{
				$values = ArrayHelper::getColumn($records, 'title', 'id');

				$seller->spl_category = json_encode($values);
			}
		}
	}

	/**
	 * Process the records for caching
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function batchProcess()
	{
		foreach ($this->sellers as $seller)
		{
			list($latP, $lngP) = explode(',', $seller->product_location . ',');
			list($latS, $lngS) = explode(',', $seller->store_location . ',');

			unset($seller->product_location, $seller->store_location);

            $registry = new Registry;

			$registry->loadObject($this->product);
			$registry->loadObject($seller);

			$registry->set('store_lat', $latS);
			$registry->set('store_lng', $lngS);
			$registry->set('product_lat', ($latP || $lngP) ? $latP : $latS);
			$registry->set('product_lng', ($latP || $lngP) ? $lngP : $lngS);

			if (!$seller->psx_country && !$seller->psx_state && !$seller->psx_district &&
				!$seller->psx_city && !$seller->psx_locality && !$seller->psx_sublocality && !$seller->psx_zip)
			{
				$registry->set('psx_country', $seller->store_country);
				$registry->set('psx_state', $seller->store_state);
				$registry->set('psx_district', $seller->store_district);
				$registry->set('psx_city', $seller->store_city);
				$registry->set('psx_locality', $seller->store_locality);
				$registry->set('psx_sublocality', $seller->store_sublocality);
				$registry->set('psx_zip', $seller->store_zip);
			}

			$related = $this->helper->relatedProduct->getByProduct($this->productId);

			$registry->set('related_products', $related ? json_encode($related) : '');

			$specP = $this->getSpecifications(0);

			$itemR = $this->makeRecord($registry, $seller, $specP);

			$this->append($itemR);

			foreach ($this->variants as $variant)
			{
				$vsx = $this->getVsx($variant->variant_id, $seller->seller_uid);

				if ($vsx)
				{
					$itemR = $this->makeRecord($registry, $seller, $specP, $variant, $vsx);

					$this->append($itemR);
				}
			}
		}

		$this->addPrices();

		EventHelper::trigger('onProcessCacheRecord', array('context' => 'com_sellacious.product', 'items' => &$this->items));

		$this->checkListable();
	}

	/**
	 * Method to populate variant level record from base product record
	 *
	 * @param   Registry  $registry  The base record object with just the common values for all variants
	 * @param   stdClass  $seller    The seller + psx record, @see  loadSellerList()
	 * @param   array     $specP     The main product specifications, @see   getSpecifications()
	 * @param   stdClass  $variant   The variant record, @see  loadVariantList()
	 * @param   stdClass  $vsx       The variant-seller attributes, @see   getVsx()
	 *
	 * @return  Registry
	 *
	 * @since   2.0.0
	 */
	protected function makeRecord(Registry $registry, $seller, $specP, $variant = null, $vsx = null)
	{
		$regR = new Registry((string) $registry);

		$regR->loadObject($variant);
		$regR->loadObject($vsx);

		$allowRate = $this->helper->config->get('product_rating');
		$variantId = $variant ? $variant->variant_id : 0;
		$sellerUid = $seller->seller_uid;

		$code       = $this->helper->product->getCode($this->productId, $variantId, $sellerUid);
		$spec       = $variantId ? $this->getSpecifications($variantId) : array();
		$orderCount = $this->helper->order->getOrderCount($this->productId, $variantId, $sellerUid);
		$orderUnits = $this->helper->order->getOrderCount($this->productId, $variantId, $sellerUid, true);
		$rating     = $allowRate ? $this->helper->rating->getProductRating($this->productId, $variantId, $sellerUid) : '';

		$regR->set('code', $code);
		$regR->set('variant_id', $variantId);
		$regR->set('specifications', json_encode(array_merge($specP, $spec)));
		$regR->set('stock_capacity', $regR->get('stock') + $regR->get('over_stock'));
		$regR->set('product_rating', $allowRate ? json_encode($rating) : '');
		$regR->set('order_count', $orderCount);
		$regR->set('order_units', $orderUnits);

		return $regR;
	}

	/**
	 * Get a list of available specification fields
	 *
	 * @return  int[]
	 *
	 * @since   2.0.0
	 */
	protected function getFields()
	{
		static $pks = null;

		if ($pks === null)
		{
			$query = $this->db->getQuery(true);

			$query->select('a.id')
				  ->from('#__sellacious_fields a')
				  ->where('a.state = 1')
				  ->where('a.parent_id > 0')
				  ->where('a.context = ' . $this->db->q('product'))
				  ->where('a.type != ' . $this->db->q('fieldgroup'))
				  ->order('a.lft ASC');

			$pks = (array) $this->db->setQuery($query)->loadColumn();
		}

		return $pks;
	}

	/**
	 * Method to get the specifications for the active product and given one of its variants
	 *
	 * @param   int  $variantId  Variant id to query for
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getSpecifications($variantId)
	{
		$filter = array(
			'list.select' => 'a.field_id, a.field_value, a.is_json',
			'list.from'   => '#__sellacious_field_values',
			'field_id'    => $this->getFields(),
		);

		if ($variantId == 0)
		{
			$filter['table_name'] = 'products';
			$filter['record_id']  = (int) $this->productId;
		}
		else
		{
			$filter['table_name'] = 'variants';
			$filter['record_id']  = (int) $variantId;
		}

		$iterator = $this->helper->field->getIterator($filter);
		$values   = array();

		foreach ($iterator as $obj)
		{
			$col = sprintf('f%d', $obj->field_id);

			$values[$col] = $obj->is_json ? json_decode($obj->field_value) : $obj->field_value;
		}

		return $values;
	}

	/**
	 * Get the manufacturer information for the given manufacturer id
	 *
	 * @param   int  $id  Manufacturer uid
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	protected function getManufacturer($id)
	{
		static $cache = array();

		if (!isset($cache[$id]))
		{
			try
			{
				$query = $this->db->getQuery(true);

				$query->select('u.name')
					  ->from($this->db->quoteName('#__users', 'u'))
					  ->where('u.id = ' . (int) $id);

				$query->select('m.title m_company, m.category_id m_catid, m.code m_code')
					->join('left', $this->db->quoteName('#__sellacious_manufacturers', 'm') . ' ON m.user_id = u.id');

				$user = $this->db->setQuery($query)->loadObject();

				$cache[$id] = $user ?: false;
			}
			catch (Exception $e)
			{
				// Ignore

				$cache[$id] = false;
			}
		}

		return $cache[$id] ?: null;
	}

	/**
	 * Get the variant seller attributes
	 *
	 * @param   int  $variantId
	 * @param   int  $sellerUid
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	protected function getVsx($variantId, $sellerUid)
	{
		$query = $this->db->getQuery(true);
		$cols  = array(
			'vsx_id'                 => 'a.id',
			'variant_price_mod'      => 'a.price_mod',
			'variant_price_mod_perc' => 'a.price_mod_perc',
			'stock'                  => 'a.stock',
			'over_stock'             => 'a.over_stock',
			'stock_reserved'         => 'a.stock_reserved',
			'stock_sold'             => 'a.stock_sold',
			'is_selling_variant'     => 'a.state',
		);

		$query->select($this->db->quoteName(array_values($cols), array_keys($cols)))
			  ->from($this->db->quoteName('#__sellacious_variant_sellers', 'a'))
			  ->where('a.variant_id = ' . (int) $variantId)
			  ->where('a.seller_uid = ' . (int) $sellerUid);

		return $this->db->setQuery($query)->loadObject();
	}

	/**
	 * Get seller-specific prices for this product. Please note that all amounts are in seller's currency.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addPrices()
	{
		$pTypes = array();

		// Collate by pricing type
		foreach ($this->items as $item)
		{
			$pt = $item->get('pricing_type');

			$pTypes[$pt] = true;
		}

		foreach ($pTypes as $pt => $true)
		{
			$handler = PriceHelper::getHandler($pt);
			$handler->setPricesForCache($this->productId, $this->items);
		}
	}

	/**
	 * Method to check items that can be display in the list view on account of
	 * special listing or best price and grouped display of sellers and variants of a product
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function checkListable()
	{
		$list             = array();
		$array            = array();
		$seller_separate  = $this->helper->config->get('multi_seller') == 2;
		$variant_separate = $this->helper->config->get('multi_variant') == 2;

		if ($variant_separate && $seller_separate)
		{
			$list = $this->items;
		}
		elseif ($variant_separate)
		{
			foreach ($this->items as $item)
			{
				$vid = $item->get('variant_id');
				$uid = $item->get('seller_uid');

				$array[$vid][$uid] = $item;
			}

			static::bestSellerOfEachVariant($array, $list);
		}
		else
		{
		    $tmp = array();

			foreach ($this->items as $item)
			{
				$vid = $item->get('variant_id');
				$uid = $item->get('seller_uid');

				if ($vid > 0 && !$variant_separate)
				{
					$item->set('is_visible', 0);
				}

				$array[$uid][$vid] = $item;
			}

			static::bestVariantOfEachSeller($array, $tmp);

			if ($seller_separate)
			{
				$list = $tmp;
			}
			else
			{
				static::bestSeller($tmp, $list);
			}
		}

		foreach ($list as $item)
		{
			$item->set('is_visible', $item->get('spl_category') ? 2 : 1);
		}
	}

	/**
	 * Method to find the best seller items among given items grouped by variant
	 * The minimum price item is taken also
	 *
	 * @param   Registry[][]  $itemsList
	 * @param   Registry[]    $list
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function bestSellerOfEachVariant(array &$itemsList, &$list = array())
	{
		foreach ($itemsList as $vid => $items)
		{
			static::bestSeller($items, $list);
		}
	}

	/**
	 * Method to find the best variant items among given items grouped by seller
	 * The minimum price item is taken also
	 *
	 * @param   Registry[][]  $itemsList
	 * @param   Registry[]    $list
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function bestVariantOfEachSeller(array &$itemsList, &$list = array())
	{
		foreach ($itemsList as $uid => $items)
		{
			static::bestVariant($items, $list);
		}
	}

	/**
	 * Method to find the best variant item among given items
	 * The minimum price item is taken also
	 *
	 * @param   Registry[]  $items
	 * @param   Registry[]  $list
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function bestVariant(array &$items, &$list = array())
	{
		$tmp = null;

		foreach ($items as $item)
		{
			if (!$tmp || $item['sales_price'] < $tmp['sales_price'])
			{
				$tmp = $item;
			}
		}

		if ($tmp)
		{
			$list[] = $tmp;
		}
	}

	/**
	 * Method to find the best seller item among given items
	 * Special category listed are always taken, and the minimum price item is taken also
	 *
	 * @param   Registry[]  $items
	 * @param   Registry[]  $list
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected static function bestSeller(array &$items, &$list = array())
	{
		$tmp = null;
		$use = false;

		foreach ($items as $item)
		{
			if ($item['spl_category'])
			{
				$use = false;
				$tmp = $item;

				$list[] = $item;
			}
			elseif (!$tmp || $item['sales_price'] < $tmp['sales_price'])
			{
				$use = true;
				$tmp = $item;
			}
		}

		if ($use)
		{
			$list[] = $tmp;
		}
	}

	/**
	 * Add a record to the result list
	 *
	 * @param   Registry  $registry
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function append(Registry $registry)
	{
		foreach (static::$columns as $column)
		{
			$registry->def($column, '');
		}

		$this->items[] = $registry;
	}
}
