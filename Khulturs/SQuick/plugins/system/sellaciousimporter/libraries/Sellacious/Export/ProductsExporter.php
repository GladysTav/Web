<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Export;

// no direct access
defined('_JEXEC') or die;

use JFactory;
use JObject;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Media\Image\Image;
use Sellacious\Media\Storage\MediaDatabaseTable;
use Sellacious\Product;
use Sellacious\Utilities\Timer;

/**
 * Import utility class
 *
 * @since   1.5.0
 */
class ProductsExporter
{
	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $name = 'Products';

	/**
	 * @var    array
	 *
	 * @since   1.5.0
	 */
	protected $options = array();

	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since   1.5.0
	 */
	protected $db;

	/**
	 * @var    \SellaciousHelper
	 *
	 * @since   1.5.0
	 */
	protected $helper;

	/**
	 * @var    \JEventDispatcher
	 *
	 * @since   1.5.0
	 */
	protected $dispatcher;

	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $filename;

	/**
	 * @var    resource
	 *
	 * @since   1.5.0
	 */
	protected $fp;

	/**
	 * @var    Timer
	 *
	 * @since   1.5.0
	 */
	public $timer;

	/**
	 * The actual CSV headers found in the uploaded file (always processed in the same character case as provided in the CSV)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $headers = array();

	/**
	 * The internal key names for the CSV columns (always processed in lowercase)
	 *
	 * @var    string[]
	 *
	 * @since   1.5.0
	 */
	protected $fields = array();

	/**
	 * @var   ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	protected $loader;

	/**
	 * @var   array
	 *
	 * @since   2.0.0
	 */
	protected $filters = array();

	/**
	 * Constructor
	 *
	 * @since   1.5.0
	 */
	public function __construct()
	{
		$this->db         = \JFactory::getDbo();
		$this->helper     = \SellaciousHelper::getInstance();
		$this->dispatcher = $this->helper->core->loadPlugins();
		$this->timer      = Timer::getInstance('Export.' . $this->name);
		$this->loader     = new ProductsCacheReader;
	}

	/**
	 * Set the import configuration options
	 *
	 * @param   string  $key    The name of the parameter to set
	 * @param   mixed   $value  The new value
	 *
	 * @return  static
	 *
	 * @since   1.5.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Get the import configuration options
	 *
	 * @param   string  $key  The name of the parameter to set
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * Get the fields headings for the CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getHeaders()
	{
		if (!$this->headers)
		{
			$this->getColumns();
		}

		return $this->headers;
	}

	/**
	 * Get the fields for the CSV
	 *
	 * @return  \string[]
	 *
	 * @since   1.5.0
	 */
	public function getFields()
	{
		if (!$this->fields)
		{
			$this->getColumns();
		}

		return $this->fields;
	}

	/**
	 * Prepare the output environment
	 *
	 * @param   string  $filename
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 *
	 * @throws  \Exception
	 */
	protected function prepare($filename)
	{
		ignore_user_abort(true);

		if (substr($filename, -4) != '.csv')
		{
			$filename .= '.csv';
		}

		$fp = fopen($filename, 'w');

		if (!$fp)
		{
			throw new \Exception(\JText::sprintf('COM_SELLACIOUS_EXPORT_ERROR_FILE_COULD_NOT_OPEN', basename($filename)));
		}

		$this->filename = $filename;
		$this->fp       = $fp;
	}

	/**
	 * Get the columns for the products import CSV template for the given categories if any, or a basic one without any specifications
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getColumns()
	{
		$allow_return   = $this->helper->config->get('purchase_return', 0);
		$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

		// Omitted for now: 'EPRODUCT_USAGE_LICENSE', 'MANUFACTURER_CATEGORY', 'SELLER_CATEGORY'
		$columns = array(
			'product_alias'          => 'PRODUCT_UNIQUE_ALIAS',
			'product_title'          => 'PRODUCT_TITLE',
			'product_type'           => 'PRODUCT_TYPE',
			'product_sku'            => 'PRODUCT_SKU',
			'manufacturer_sku'       => 'MFG_ASSIGNED_SKU',
			'product_introtext'      => 'PRODUCT_SUMMARY',
			'product_description'    => 'PRODUCT_DESCRIPTION',
			'p_stock'                => 'PRODUCT_CURRENT_STOCK',
			'p_over_stock'           => 'PRODUCT_OVER_STOCK_SALE_LIMIT',
			'p_stock_reserved'       => 'PRODUCT_RESERVED_STOCK',
			'p_stock_sold'           => 'PRODUCT_STOCK_SOLD',
			'product_feature_1'      => 'PRODUCT_FEATURE_1',
			'product_feature_2'      => 'PRODUCT_FEATURE_2',
			'product_feature_3'      => 'PRODUCT_FEATURE_3',
			'product_feature_4'      => 'PRODUCT_FEATURE_4',
			'product_feature_5'      => 'PRODUCT_FEATURE_5',
			'product_active'         => 'PRODUCT_STATE',
			'product_ordering'       => 'PRODUCT_ORDERING',
			'length'                 => 'LENGTH',
			'width'                  => 'WIDTH',
			'height'                 => 'HEIGHT',
			'weight'                 => 'WEIGHT',
			'vol_weight'             => 'VOLUMETRIC_WEIGHT',
			'shipping_length'        => 'SHIPPING_LENGTH',
			'shipping_width'         => 'SHIPPING_WIDTH',
			'shipping_height'        => 'SHIPPING_HEIGHT',
			'shipping_weight'        => 'SHIPPING_WEIGHT',
			'delivery_mode'          => 'EPRODUCT_DELIVERY_MODE',
			'download_limit'         => 'EPRODUCT_DOWNLOAD_LIMIT',
			'download_period'        => 'EPRODUCT_DOWNLOAD_PERIOD',
			'preview_url'            => 'EPRODUCT_PREVIEW_URL',
			'package_items'          => 'PACKAGE_ITEMS',
			'listing_type'           => 'PRODUCT_LISTING_TYPE',
			'item_condition'         => 'PRODUCT_CONDITION',
			'whats_in_box'           => 'WHATS_IN_BOX',
			'quantity_min'           => 'MIN_ORDER_QTY',
			'quantity_max'           => 'MAX_ORDER_QTY',
			'flat_shipping'          => 'IS_FLAT_SHIPPING',
			'shipping_flat_fee'      => 'FLAT_SHIPPING_FEE',
			'manufacturer_name'      => 'MANUFACTURER_NAME',
			'manufacturer_username'  => 'MANUFACTURER_USERNAME',
			'manufacturer_code'      => 'MANUFACTURER_CODE',
			'manufacturer_company'   => 'MANUFACTURER_COMPANY',
			'manufacturer_email'     => 'MANUFACTURER_EMAIL',
			'metakey'                => 'PRODUCT_META_KEY',
			'metadesc'               => 'PRODUCT_META_DESCRIPTION',
			'listing_purchased'      => 'LISTING_PURCHASE_DATE',
			'listing_start'          => 'LISTING_START_DATE',
			'listing_end'            => 'LISTING_END_DATE',
			'seller_sku'             => 'SELLER_SKU',
			'pricing_type'           => 'PRICING_TYPE',
			'seller_currency'        => 'PRICE_CURRENCY',
			'price_list_price'       => 'PRICE_LIST_PRICE',
			'price_cost_price'       => 'PRICE_COST_PRICE',
			'price_margin'           => 'PRICE_MARGIN',
			'price_margin_percent'   => 'PRICE_MARGIN_PERCENT',
			'price_amount_flat'      => 'PRICE_AMOUNT_FLAT',
			'variant_id'             => 'VARIANT_UNIQUE_ALIAS',
			'variant_title'          => 'VARIANT_TITLE',
			'variant_sku'            => 'VARIANT_SKU',
			'variant_feature_1'      => 'VARIANT_FEATURE_1',
			'variant_feature_2'      => 'VARIANT_FEATURE_2',
			'variant_feature_3'      => 'VARIANT_FEATURE_3',
			'variant_feature_4'      => 'VARIANT_FEATURE_4',
			'variant_feature_5'      => 'VARIANT_FEATURE_5',
			'v_stock'                => 'VARIANT_CURRENT_STOCK',
			'v_over_stock'           => 'VARIANT_OVER_STOCK_SALE_LIMIT',
			'v_stock_reserved'       => 'VARIANT_RESERVED_STOCK',
			'v_stock_sold'           => 'VARIANT_STOCK_SOLD',
			'variant_price_mod'      => 'VARIANT_PRICE_ADD',
			'variant_price_mod_perc' => 'VARIANT_PRICE_IS_PERCENT',
			'image_url'              => 'IMAGE_URL',
			'image_folder'           => 'IMAGE_FOLDER',
			'image_filename'         => 'IMAGE_FILENAME',
			'related_product_groups' => 'RELATED_PRODUCT_GROUPS',
		);

		if ($allow_return == 2)
		{
			$columns['return_days'] = 'ORDER_RETURN_DAYS';
			$columns['return_tnc']  = 'ORDER_RETURN_TNC';
		}

		if ($allow_exchange == 2)
		{
			$columns['exchange_days'] = 'ORDER_EXCHANGE_DAYS';
			$columns['exchange_tnc']  = 'ORDER_EXCHANGE_TNC';
		}

		if ($this->helper->access->check('user.edit'))
		{
			$sellerColumns = array(
				'seller_name'     => 'SELLER_NAME',
				'seller_username' => 'SELLER_USERNAME',
				'seller_email'    => 'SELLER_EMAIL',
				'seller_company'  => 'SELLER_BUSINESS_NAME',
				'seller_code'     => 'SELLER_CODE',
				'seller_mobile'   => 'SELLER_MOBILE',
				'seller_website'  => 'SELLER_WEBSITE',
				'store_name'      => 'SELLER_STORE_NAME',
				'store_address'   => 'SELLER_STORE_ADDRESS',
				'store_location'  => 'STORE_LATITUDE_LONGITUDE',
			);

			foreach ($sellerColumns as $key => $sellerColumn)
			{
				$columns[$key] = $sellerColumn;
			}
		}

		$countPr = $this->getPriceCount();
		$countCt = $this->getCategoryCount();
		$countSp = $this->getSplCategoryCount();

		$this->setOption('price_count', $countPr);
		$this->setOption('category_count', $countCt);
		$this->setOption('spl_category_count', $countSp);

		$columns['category_titles'] = 'PRODUCT_CATEGORIES';

		for ($p = 1; $p <= $countCt; $p++)
		{
			$columns['category_' . $p] = 'CATEGORY_' . $p;
		}

		$columns['spl_category_titles'] = 'SPECIAL_CATEGORIES';

		for ($p = 1; $p <= $countSp; $p++)
		{
			$columns['splcategory_' . $p] = 'SPLCATEGORY_' . $p;
		}

		for ($p = 1; $p <= $countPr; $p++)
		{
			$columns['price_' . $p . '_list_price']        = 'PRICE_' . $p . '_LIST_PRICE';
			$columns['price_' . $p . '_cost_price']        = 'PRICE_' . $p . '_COST_PRICE';
			$columns['price_' . $p . '_margin']            = 'PRICE_' . $p . '_MARGIN';
			$columns['price_' . $p . '_margin_percent']    = 'PRICE_' . $p . '_MARGIN_PERCENT';
			$columns['price_' . $p . '_amount_flat']       = 'PRICE_' . $p . '_AMOUNT_FLAT';
			$columns['price_' . $p . '_start_date']        = 'PRICE_' . $p . '_START_DATE';
			$columns['price_' . $p . '_end_date']          = 'PRICE_' . $p . '_END_DATE';
			$columns['price_' . $p . '_min_quantity']      = 'PRICE_' . $p . '_MIN_QUANTITY';
			$columns['price_' . $p . '_max_quantity']      = 'PRICE_' . $p . '_MAX_QUANTITY';
			$columns['price_' . $p . '_client_categories'] = 'PRICE_' . $p . '_CLIENT_CATEGORIES';
		}

		$fields = $this->getSpecFields();

		foreach ($fields as $field)
		{
			$field->title  = strtoupper(preg_replace('/[^0-9a-z]+/i', '_', $field->title));
			$specColumn    = 'SPEC_' . $field->field_id . '_' . $field->title;
			$key           = strtolower($specColumn);
			$columns[$key] = $specColumn;
		}

		// Let the plugins add custom columns
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onFetchExportColumns', array('com_sellacious.export.products', &$columns, $this));

		$this->headers = array_values($columns);
		$this->fields  = array_keys($columns);

		return $columns;
	}

	/**
	 * Load the CSV file and the alias options if any, for the further processing
	 *
	 * @param   string  $filename  The absolute file path for the CSV
	 * @param   array   $aliases   The import template header aliases to map this export headers with
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function export($filename, $aliases = null)
	{
		$this->prepare($filename);

		$headers = $this->getHeaders();
		$row     = $this->applyAlias($headers, $aliases, true);

		// First row contains column headers
		fputcsv($this->fp, $row);

		$query   = $this->loader->getQuery(true);
		$filters = new JObject($this->filters);
		$this->filterSearch($this->loader, $filters);

		$ordering = $filters->get('list.fullordering', 'listing_start DESC');

		if (trim($ordering))
		{
			$query->order($query->escape($ordering));
		}

		$iterator = $this->loader->getIterator();

		foreach ($iterator as $item)
		{
			$row = $this->processRecord($item);
			$row = $this->applyAlias($row, $aliases);

			if (count($row))
			{
				fputcsv($this->fp, $row);
			}
		}

		fclose($this->fp);
	}

	/**
	 * Filter the list query by search text and other filters
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   JObject              $filters
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function filterSearch($loader, $filters)
	{
		$search = $filters->get('filter.search');

		if ($search)
		{
			if (stripos($search, 'id:') === 0)
			{
				$loader->filterValue('product_id', (int) substr($search, 3));
			}
			elseif (stripos(strtolower($search), 'sku:') === 0)
			{
				$loader->filterValue('product_sku', substr($search, 4), 'LIKE');
			}
			elseif (stripos(strtolower($search), 't:') === 0)
			{
				$loader->filterValue('product_title', substr($search, 2), 'LIKE');
			}
			else
			{
				$query  = $loader->getQuery();
				$search = $query->q('%' . $query->e($search, true) . '%', false);

				$query->where(sprintf('(product_sku LIKE %1$s OR product_title LIKE %1$s OR product_description LIKE %1$s)', $search));
			}
		}

		// Filter by price display
		$pricing_type = $filters->get('filter.pricing_type');

		if ($pricing_type)
		{
			$loader->filterValue('pricing_type', $pricing_type);
		}

		// Filter by selling state
		$selling = $filters->get('list.selling');

		if (is_numeric($selling))
		{
			$loader->filterValue('is_selling', (int) $selling);
		}

		// Filter by published state
		$state = $filters->get('filter.state');

		if (is_numeric($state))
		{
			$loader->filterValue('product_active', (int) $state);
		}

		// Filter by category
		if ($categoryId = $filters->get('filter.category'))
		{
			$loader->filterInJsonKey('category_ext', null, $categoryId);
		}

		if ($type = $filters->get('filter.type'))
		{
			$loader->filterValue('product_type', $type);
		}

		// Filter by manufacturer
		if ($manufacturerId = $filters->get('filter.manufacturer'))
		{
			$loader->filterValue('manufacturer_id', (int) $manufacturerId);
		}

		// Filter by seller
		if ($this->helper->access->check('product.list'))
		{
			if ($seller_uid = $filters->get('filter.seller_uid'))
			{
				$loader->filterValue('seller_uid', (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('product.list.own'))
		{
			$loader->getQuery()->where(sprintf('(seller_uid = %1$d OR owner_uid = %1$d)', JFactory::getUser()->id));
		}
		else
		{
			$loader->fallacy();
		}

		// Filter by language
		if ($lang = $filters->get('filter.language'))
		{
			$loader->filterValue('language', $lang);
		}
	}

	/**
	 * Method to import a single record obtained from the CSV
	 *
	 * @param   \stdClass  $obj  The record object to be exported from the cache table
	 *
	 * @return  string[]  Whether the record was imported successfully
	 *
	 * @since   1.5.0
	 */
	protected function processRecord($obj)
	{
		$user   = JFactory::getUser();
		$userId = $user->id;
		$row    = array();
		$fields = $this->getFields();

		$listOwn       = $this->helper->access->check('product.edit.seller.own') && $obj->seller_uid == $userId;
		$listingAccess = $listOwn || $this->helper->access->check('product.edit.seller');
		$allowed       = array('product_title', 'product_alias', 'product_type', 'product_sku');

		// First populate all fields in correct sequence
		foreach ($fields as $key)
		{
			$columnAllowed     = in_array($key, $allowed) || $listingAccess ;
			$canAccessProperty = property_exists($obj, $key) && $columnAllowed;
			$row[$key]         = $canAccessProperty ? $obj->$key : null;
		}

		$row['product_sku']    = isset($obj->local_sku) ? $obj->local_sku : '';
		$row['product_active'] = $obj->product_active == 1 ? 'PUBLISHED' : $obj->product_active;

		if (in_array('store_name', $fields))
		{
			$row['store_name'] = isset($obj->seller_store) ? $obj->seller_store : '';
		}

		if (!isset($item->category_ids) && property_exists($obj, 'category'))
		{
			$cats = json_decode($obj->category, true) ?: array();

			$obj->categories   = array_keys($cats);
			$obj->category_ids = implode(',', $obj->categories);
		}

		if (!isset($item->spl_category_titles) && property_exists($obj, 'spl_category'))
		{
			$cat2 = json_decode($obj->spl_category, true) ?: array();

			$obj->spl_categories   = array_keys($cat2);
			$obj->spl_category_ids = implode(',', $obj->spl_categories);
		}

		// Stock info
		if ($obj->variant_id)
		{
			$row['v_stock']          = $listingAccess ? $obj->stock : '';
			$row['v_over_stock']     = $listingAccess ? $obj->over_stock : '';
			$row['v_stock_reserved'] = $listingAccess ? $obj->stock_reserved : '';
			$row['v_stock_sold']     = $listingAccess ? $obj->stock_sold : '';
		}
		else
		{
			$row['p_stock']          = $listingAccess ? $obj->stock : '';
			$row['p_over_stock']     = $listingAccess ? $obj->over_stock : '';
			$row['p_stock_reserved'] = $listingAccess ? $obj->stock_reserved : '';
			$row['p_stock_sold']     = $listingAccess ? $obj->stock_sold : '';
		}

		// Product features
		if ($obj->product_features)
		{
			$features = json_decode($obj->product_features);

			foreach ($features as $key => $feature)
			{
				$row['product_feature_' . ($key + 1)] = $feature;
			}
		}

		// Variant features
		if ($obj->variant_features)
		{
			$v_features = json_decode($obj->variant_features);

			foreach ($v_features as $key => $v_feature)
			{
				$row['variant_feature_' . ($key + 1)] = $v_feature;
			}
		}

		// Package items
		if ($obj->product_type == 'package')
		{
			$packageItems = $this->helper->package->getProducts($obj->product_id);
			$packageCodes = array();

			if ($packageItems)
			{
				foreach ($packageItems as $packageItem)
				{
					$packageCodes[] = $this->helper->product->getCode($packageItem->product_id, $packageItem->variant_id, $obj->seller_uid);
				}

				$obj->package_items = implode(';', $packageCodes);
			}
		}

		// Fields: category_titles = category_ids, spl_category_titles = spl_category_ids
		$categories    = $this->getCategoryLevels(explode(',', $obj->category_ids));
		$splCategories = $this->getSplCategoryLevels(explode(',', $obj->spl_category_ids));
		$pricesF       = $this->getPrices($obj->product_id, $obj->seller_uid, true);
		$pricesA       = $this->getPrices($obj->product_id, $obj->seller_uid, false);
		$countPr       = $this->getPriceCount();

		$row['category_titles']     = implode(';', $categories);
		$row['spl_category_titles'] = implode(';', $splCategories);

		// Fields: PRICE_LIST_PRICE, PRICE_COST_PRICE, PRICE_MARGIN, PRICE_MARGIN_PERCENT, PRICE_AMOUNT_FLAT
		if ($pricesF)
		{
			$price = reset($pricesF);

			$row['price_list_price']     = $listingAccess ? $price->price_list_price : '';
			$row['price_cost_price']     = $listingAccess ? $price->price_cost_price : '';
			$row['price_margin']         = $listingAccess ? $price->price_margin : '';
			$row['price_margin_percent'] = $listingAccess ? $price->price_margin_percent : '';
			$row['price_amount_flat']    = $listingAccess ? $price->price_amount_flat : '';
		}

		// Fields: LIST_PRICE, COST_PRICE, MARGIN, MARGIN_PERCENT, AMOUNT_FLAT, START_DATE, END_DATE, MIN_QUANTITY, MAX_QUANTITY
		if ($listingAccess)
		{
			foreach ($pricesA as $index => $price)
			{
				$row['price_' . ($index + 1) . '_list_price']        = $price->list_price;
				$row['price_' . ($index + 1) . '_cost_price']        = $price->cost_price;
				$row['price_' . ($index + 1) . '_margin']            = $price->margin;
				$row['price_' . ($index + 1) . '_margin_percent']    = $price->margin_percent;
				$row['price_' . ($index + 1) . '_amount_flat']       = $price->amount_flat;
				$row['price_' . ($index + 1) . '_start_date']        = $price->start_date;
				$row['price_' . ($index + 1) . '_end_date']          = $price->end_date;
				$row['price_' . ($index + 1) . '_min_quantity']      = $price->min_quantity;
				$row['price_' . ($index + 1) . '_max_quantity']      = $price->max_quantity;
				$row['price_' . ($index + 1) . '_client_categories'] = $price->client_categories;
			}
		}
		elseif ($pricesA)
		{
			for ($p = 1; $p <= $countPr; $p++)
			{
				$row['price_' . $p . '_list_price']        = '';
				$row['price_' . $p . '_cost_price']        = '';
				$row['price_' . $p . '_margin']            = '';
				$row['price_' . $p . '_margin_percent']    = '';
				$row['price_' . $p . '_amount_flat']       = '';
				$row['price_' . $p . '_start_date']        = '';
				$row['price_' . $p . '_end_date']          = '';
				$row['price_' . $p . '_min_quantity']      = '';
				$row['price_' . $p . '_max_quantity']      = '';
				$row['price_' . $p . '_client_categories'] = '';
			}
		}

		/* Fields: 'spec_N' */
		$product = new Product($obj->product_id, $obj->variant_id, $obj->seller_uid);
		$specifications = $product->getSpecifications( false);

		if ($specifications)
		{
			foreach ($specifications as $index => $field)
			{
				$field->title = strtolower(preg_replace('/[^0-9a-z]+/i', '_', $field->title));
				$key          = 'spec_' . (int) $field->id . '_' . $field->title;
				$row[$key]    = is_array($field->value) ? implode(', ', $field->value) : $field->value;
			}
		}

		// Product Images (TODO: Currently only one image is exported, export all images later)
		$images = $this->getImages($obj->product_id);

		if ($images)
		{
			$image = $images[0];

			$row['image_url']      = $image->image_url;
			$row['image_folder']   = $image->image_folder;
			$row['image_filename'] = $image->image_filename;
		}

		// Related products
		$relatedGroups = $this->helper->relatedProduct->getGroups($obj->product_id);

		if ($relatedGroups)
		{
			$groups                        = ArrayHelper::getColumn($relatedGroups, 'title');
			$row['related_product_groups'] = implode(';', $groups);
		}

		$row = $this->translate($row);

		return $row;
	}

	/**
	 * Convert the symbolic values into human readable text values for the exported CSV to be readable.
	 *
	 * @param   string[]  $row  The exportable record
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function translate($row)
	{
		$tObj = new \stdClass;

		$row['length']     = $this->helper->unit->explain(json_decode($row['length']) ?: $tObj, true);
		$row['width']      = $this->helper->unit->explain(json_decode($row['width']) ?: $tObj, true);
		$row['height']     = $this->helper->unit->explain(json_decode($row['height']) ?: $tObj, true);
		$row['weight']     = $this->helper->unit->explain(json_decode($row['weight']) ?: $tObj, true);
		$row['vol_weight'] = $this->helper->unit->explain(json_decode($row['vol_weight']) ?: $tObj, true);

		$period    = json_decode($row['download_period']) ?: $tObj;
		$number    = isset($period->l) ? $period->l : 0;
		$interval  = isset($period->p) ? $period->p : '';
		$intervals = array(
			'second' => 'Seconds',
			'minute' => 'Minutes',
			'hour'   => 'Hours',
			'day'    => 'Days',
			'week'   => 'Weeks',
			'month'  => 'Months',
			'year'   => 'Years',
		);

		$row['delivery_mode']          = ucwords($row['delivery_mode']);
		$row['download_period']        = in_array($interval, $intervals) && $number > 0 ? sprintf('%d %s', $number, $intervals[$interval]) : '';
		$row['listing_type']           = ArrayHelper::getValue(array('', 'NEW', 'USED', 'REFURBISHED'), $row['listing_type'], '');
		$row['item_condition']         = ArrayHelper::getValue(array('', 'LIKE NEW', 'AVERAGE', 'GOOD', 'POOR'), $row['item_condition'], '');
		$row['flat_shipping']          = ($row['flat_shipping'] != '') ? ($row['flat_shipping'] ? 'YES' : 'NO') : '';
		$row['price_margin_percent']   = ($row['price_margin_percent'] != '') ? ($row['price_margin_percent'] ? 'YES' : 'NO') : '';
		$row['variant_price_mod_perc'] = ($row['variant_price_mod_perc'] != '') ? ($row['variant_price_mod_perc'] ? 'YES' : 'NO') : '';

		return $row;
	}

	/**
	 * Apply the alias for the record with relevant alias. Any missing alias will cause those columns to be ignored
	 *
	 * @param   array  $record    The record to be processed
	 * @param   array  $aliases   The original => alias mapping array
	 * @param   bool   $isHeader  Flag to indicate if processing CSV header
	 *
	 * @return  array
	 *
	 * @since   1.5.2
	 */
	protected function applyAlias($record, $aliases, $isHeader = false)
	{
		if (!$aliases)
		{
			return $record;
		}

		$row = array();

		if ($isHeader)
		{
			foreach ($aliases as $original => $alias)
			{
				if (in_array($original, $record))
				{
					$row[$original] = $alias;
				}
			}
		}
		else
		{
			$headers = $this->getHeaders();

			if (count($headers) == count($record))
			{
				$values = array_values($record);

				foreach ($aliases as $original => $alias)
				{
					$index = array_search($original, $headers);

					if ($index !== false)
					{
						$row[$original] = $values[$index];
					}
				}
			}
		}

		return $row;
	}

	/**
	 * Get max number of price rows for any product listing
	 *
	 * @return  int
	 *
	 * @since   1.5.0
	 */
	protected function getPriceCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(*) as cnt')
			->from('#__sellacious_product_prices')
			->group('product_id, seller_uid')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get max number of categories for any product
	 *
	 * @return  int
	 *
	 * @since   1.5.2
	 */
	protected function getCategoryCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(category_id) as cnt')
			->from('#__sellacious_product_categories')
			->group('product_id')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get max number of categories for any product
	 *
	 * @return  int
	 *
	 * @since   1.5.2
	 */
	protected function getSplCategoryCount()
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(category_id) as cnt')
			->from('#__sellacious_seller_listing')
			->where('category_id > 0')
			->where('state = 1')
			->group('product_id, seller_uid')
			->order('cnt DESC');

		return $this->db->setQuery($query)->loadResult();
	}

	/**
	 * Get list of specification fields for any product
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function getSpecFields()
	{
		$query = $this->db->getQuery(true);

		$query->select('a.field_id')
			->from($this->db->qn('#__sellacious_field_values', 'a'))
			->where('(a.table_name = ' . $this->db->q('products') . 'OR a.table_name = ' . $this->db->q('variants') . ')');

		$query->select('f.title')
			->join('inner', $this->db->qn('#__sellacious_fields', 'f') . ' ON f.id = a.field_id')
			->where('f.parent_id > 0');

		$query->group('a.field_id');

		$specs = $this->db->setQuery($query)->loadObjectList();

		return $specs;
	}

	/**
	 * Extract the category hierarchy path from the category id
	 *
	 * @param   int[]  $pks  The category ids
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getCategoryLevels($pks)
	{
		return $this->getTreeLevels($pks, '#__sellacious_categories');
	}

	/**
	 * Extract the special category hierarchy path from the special category id
	 *
	 * @param   int[]  $pks  The special category ids
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	protected function getSplCategoryLevels($pks)
	{
		return $this->getTreeLevels($pks, '#__sellacious_splcategories');
	}

	/**
	 * Extract the hierarchy of title from the given nested table
	 *
	 * @param   int[]   $pks        The record ids to process
	 * @param   string  $tableName  The nested table name
	 *
	 * @return  string[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.0
	 */
	public function getTreeLevels($pks, $tableName)
	{
		$paths = array();
		$query = $this->db->getQuery(true);

		$query->select('b.title')
			->from($this->db->qn($tableName, 'a'));

		$query->join('left', $this->db->qn($tableName, 'b') . ' ON b.lft <= a.lft AND a.rgt <= b.rgt AND b.level > 0');

		$query->order('b.lft ASC');

		foreach ($pks as $pk)
		{
			$query->clear('where')->where('a.id = ' . (int) $pk);

			$names = $this->db->setQuery($query)->loadColumn();

			if ($names)
			{
				$paths[$pk] = implode('/', $names);
			}
		}

		return $paths;
	}

	/**
	 * Extract and save the prices columns from the record and clear them from the row
	 *
	 * @param   int   $productId  The product id
	 * @param   int   $sellerUid  The seller uid
	 * @param   bool  $fallback   Whether to load default price or advanced
	 *
	 * @return  \stdClass[]
	 *
	 * @since   1.5.0
	 */
	protected function getPrices($productId, $sellerUid, $fallback)
	{
		if ($fallback)
		{
			$columns = array(
				'a.list_price'  => 'price_list_price',
				'a.cost_price'  => 'price_cost_price',
				'a.margin'      => 'price_margin',
				'a.margin_type' => 'price_margin_percent',
				'a.ovr_price'   => 'price_amount_flat',
			);
		}
		else
		{
			$columns = array(
				'a.list_price'  => 'list_price',
				'a.cost_price'  => 'cost_price',
				'a.margin'      => 'margin',
				'a.margin_type' => 'margin_percent',
				'a.ovr_price'   => 'amount_flat',
				'a.sdate'       => 'start_date',
				'a.edate'       => 'end_date',
				'a.qty_min'     => 'min_quantity',
				'a.qty_max'     => 'max_quantity',
			);
		}

		$query = $this->db->getQuery(true);

		$query->select($this->db->qn(array_keys($columns), array_values($columns)))
			->from($this->db->qn('#__sellacious_product_prices', 'a'))
			->join('left', $this->db->qn('#__sellacious_productprices_clientcategory_xref', 'cx') . ' ON cx.product_price_id = a.id')
			->join('left', $this->db->qn('#__sellacious_categories', 'c') . ' ON c.id = cx.cat_id')
			->where('a.product_id = ' . (int) $productId)
			->where('a.seller_uid = ' . (int) $sellerUid);

		if (!$fallback)
		{
			$query->select('GROUP_CONCAT(c.title SEPARATOR \';\') AS client_categories');
		}

		$query->where('a.is_fallback = ' . (int) ($fallback ? 1 : 0));

		$prices = $this->db->setQuery($query)->loadObjectList();

		return (array) $prices;
	}

	/**
	 * Method to get product images
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return  \stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	protected function getImages($productId)
	{
		$images  = array();
		$storage = new MediaDatabaseTable('com_sellacious/products');
		$items   = $storage->getList('images', $productId);

		if ($items)
		{
			foreach ($items as $item)
			{
				$originalName = explode('/', $item->original_name);
				$filename     = end($originalName);

				array_pop($originalName);

				$folder = implode('/', $originalName);
				$img    = new Image($item->path);

				$image                 = new \stdClass();
				$image->image_url      = $img->getUrl(true);
				$image->image_folder   = $folder;
				$image->image_filename = $filename;

				$images[] = $image;
			}
		}

		return $images;
	}

	/**
	 * Method to set filters
	 *
	 * @param   array  $filters
	 *
	 * @since   2.0.0
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
	}
}
