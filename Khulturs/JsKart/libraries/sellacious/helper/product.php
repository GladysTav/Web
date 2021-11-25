<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;

/**
 * Sellacious product helper
 *
 * @since   1.0.0
 */
class SellaciousHelperProduct extends SellaciousHelperBase
{
	/**
	 * Retrieve a list of all category ids that a given product belongs to
	 *
	 * @param   int   $product_id  Product id
	 * @param   bool  $all         Include the inherited categories
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getCategories($product_id, $all = false)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('c.category_id')
			->from($db->qn('#__sellacious_product_categories', 'c'))
			->where('product_id = ' . $db->q($product_id));

		$db->setQuery($query);
		$categories = $db->loadColumn();

		if ($all)
		{
			$categories = $this->helper->category->getParents($categories, true);
		}

		return (array) $categories;
	}

	/**
	 * Extract the category hierarchy path from the product id
	 *
	 * @param   int   $product_id  Product id
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function getCategoriesLevels($product_id)
	{
		$categories   = array();
		$category_ids = (array) $this->getCategories($product_id);

		if (count($category_ids) > 0)
		{
			$categories = $this->helper->category->getTreeLevels($category_ids);
		}

		return $categories;
	}

	/**
	 * Assign selected product to given categories un-assign from others
	 *
	 * @param   int        $productId   Product Id of the product to be added
	 * @param   int|int[]  $categories  Target categories, other associations will be removed
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setCategories($productId, $categories)
	{
		$current    = $this->getCategories($productId);
		$categories = (array) $categories;

		$remove = array_diff($current, $categories);
		$addNew = array_diff($categories, $current);

		$this->removeCategories($productId, $remove);
		$this->addCategories($productId, $addNew);
	}

	/**
	 * Method to remove category(ies) from a product
	 *
	 * @param   int        $product_id  Product id in concern
	 * @param   int|int[]  $categories  Category id or array of it to be removed
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function removeCategories($product_id, $categories)
	{
		$categories = ArrayHelper::toInteger((array) $categories);

		if (count($categories) == 0)
		{
			return;
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__sellacious_product_categories')
			->where('product_id = ' . $this->db->q($product_id))
			->where($this->db->qn('category_id') . ' IN (' . implode(',', $this->db->q($categories)) . ')');

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Method to add category(ies) to a product, in addition to any existing categories
	 *
	 * @param   int        $product_id  Product id in concern
	 * @param   int|int[]  $categories  Category id or array of it to be removed
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function addCategories($product_id, $categories)
	{
		$categories = ArrayHelper::toInteger((array) $categories);

		if (count($categories) == 0)
		{
			return;
		}

		if ($this->count(array('id' => $product_id)) == 0)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_FOUND'));
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->insert('#__sellacious_product_categories')
			->columns(array('product_id', 'category_id'));

		foreach ($categories as $category_id)
		{
			$filters = array(
				'list.from'   => '#__sellacious_product_categories',
				'product_id'  => $product_id,
				'category_id' => $category_id,
			);

			if (!$this->count($filters))
			{
				$query->values($db->q($product_id) . ', ' . $db->q($category_id));
			}
		}

		$db->setQuery($query)->execute();
	}

	/**
	 * Save the spec attributes of a product
	 *
	 * @param   int    $product_id  Product id in concern
	 * @param   array  $attributes  Associative array of spec field id and field value
	 * @param   bool   $reset       Remove current values before inserting
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setSpecifications($product_id, array $attributes, $reset = true)
	{
		if ($reset)
		{
			$this->helper->field->clearValue('products', $product_id, array_keys($attributes), true);
		}

		foreach ($attributes as $field_id => $value)
		{
			$this->helper->field->setValue('products', $product_id, $field_id, $value);
		}
	}

	/**
	 * Get seller specific information for combination of a product and a seller
	 *
	 * @param   int  $product_id  Product Id in concern
	 * @param   int  $seller_uid  Selected seller
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getSeller($product_id, $seller_uid)
	{
		/** @var  SellaciousTableProductSeller  $table */
		$query = $this->db->getQuery(true);
		$table = $this->getTable('ProductSeller');

		$query->select('a.*')
			->from($this->db->qn($table->getTableName(), 'a'))
			->where('a.product_id = ' . (int) $product_id)
			->where('a.seller_uid = ' . (int) $seller_uid);

		$query->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid');

		$query->select('s.category_id, s.title AS company, s.code AS seller_code')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id');

		try
		{
			$seller = $this->db->setQuery($query)->loadObject();

			if ($seller)
			{
				$table->parseJson($seller);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLER_FAILED', $e->getMessage()));
		}

		if (!$seller)
		{
			$seller = (object) $table->getProperties();

			$seller->name        = '';
			$seller->username    = '';
			$seller->email       = '';
			$seller->category_id = '';
			$seller->company     = '';
			$seller->seller_code = '';
		}

		return $seller;
	}

	/**
	 * Get a list of all sellers for a given product
	 *
	 * @param   int   $product_id    Product id in concern
	 * @param   bool  $only_enabled  Whether to load published only records
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getSellers($product_id, $only_enabled = true)
	{
		$query = $this->db->getQuery(true);
		$table = $this->getTable('ProductSeller');

		$query->select('a.*')
			->from($this->db->qn('#__sellacious_product_sellers', 'a'))
			->where('a.product_id = ' . $this->db->q($product_id))
			->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid')
			->select('s.category_id, s.title AS company, s.code AS seller_code, s.store_name')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id')
			->select('sp.currency, sp.mobile')
			->join('left', '#__sellacious_profiles sp ON sp.user_id = s.user_id');

		// Load desired properties from product table
		$query->select('p.type')
			->join('left', '#__sellacious_products AS p ON p.id = a.product_id')
			->group(array('a.product_id', 'a.seller_uid'));

		if ($only_enabled)
		{
			$query->where('a.state = 1');
			$query->where('u.block = 0');

			$nullDt = $this->db->getNullDate();
			$now    = JFactory::getDate()->toSql();

			if (!$this->helper->config->get('free_listing'))
			{
				$query->join('INNER', $this->db->qn('#__sellacious_seller_listing', 'l') . ' ON l.product_id = a.product_id AND l.seller_uid = a.seller_uid')
					->where('l.category_id = 0')
					->where('l.publish_up != ' . $this->db->q($nullDt))
					->where('l.publish_down != ' . $this->db->q($nullDt))
					->where('l.publish_up < ' . $this->db->q($now))
					->where('l.publish_down > ' . $this->db->q($now))
					->where('l.state = 1');
			}
		}

		try
		{
			$this->db->setQuery($query);

			$objects = $this->db->loadObjectList();

			if (is_array($objects))
			{
				array_walk($objects, array($table, 'parseJson'));
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLERS_FAILED', $e->getMessage()));
		}

		return (array) $objects;
	}

	/**
	 * Get a list of all variants of a product along with their custom attributes
	 *
	 * @param   int     $productId     Product id for which variants are required
	 * @param   bool    $full_field    Whether to list full field info along with the values or just id => value pair
	 * @param   bool    $enabled_only  Only get published variants
	 * @param   string  $language      Language to which variant fields need to be translated to
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getVariants($productId, $full_field = false, $enabled_only = false, $language = null)
	{
		$filters = array('product_id' => $productId);

		if ($enabled_only)
		{
			$filters['state'] = 1;
		}

		$variants = $this->helper->variant->loadObjectList($filters);

		// Preload product fields for the getSpecifications call to save repetitive evaluating inside it.
		$fields   = $this->helper->product->getFields($productId, array('variant'));

		foreach ($variants as $variant)
		{
			$variant->fields = $this->helper->variant->getSpecifications($variant->id, $fields, $full_field, $language);
		}

		return $variants;
	}

	/**
	 * Get List of attachments for a given product
	 *
	 * @param   int  $productId  Product id of the item
	 * @param   int  $variantId  Variant id of the item
	 * @param   int  $sellerUid  Seller user id of the item
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	public function getAttachments($productId, $variantId = 0, $sellerUid = null)
	{
		$itemsS    = array();
		$tableName = 'products';
		$context   = 'attachments';

		if ($sellerUid)
		{
			$filter = array(
				'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
				'list.join'  => array(
					array('inner', '#__sellacious_product_sellers AS psx ON psx.id = a.record_id'),
				),
				'list.where' => array(
					'psx.product_id = ' . (int) $productId,
					'psx.seller_uid = ' . (int) $sellerUid,
				),
				'table_name' => 'product_sellers',
				'context'    => $context,
				'state'      => 1,
			);
			$itemsS = (array) $this->helper->media->loadObjectList($filter);
		}

		$filter = array(
			'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
			'record_id'  => $productId,
			'table_name' => $tableName,
			'context'    => $context,
			'state'      => 1,
		);
		$itemsB = (array) $this->helper->media->loadObjectList($filter);

		$pFiles = $this->helper->media->getFilesFromPattern($tableName, $context, array($this, 'replaceCode'), array($productId, $variantId, $sellerUid));

		$items  = array_merge($itemsB, $pFiles, $itemsS);

		return $items;
	}

	/**
	 * Replace code from path.
	 *
	 * @param   string  $path       Attachment path
	 * @param   int     $productId  Product id of the item
	 * @param   int     $variantId  Variant id of the item
	 * @param   int     $sellerUid  Seller user id of the item
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.1
	 */
	public function replaceCode($path, $productId, $variantId, $sellerUid)
	{
		$flatCols = array(
			'product_id', 'variant_id', 'seller_uid', 'code',
			'product_title', 'product_type', 'product_sku',
			'variant_title', 'variant_alias', 'variant_sku',
			'seller_name', 'seller_username', 'seller_company', 'seller_code',
			'manufacturer_sku', 'manufacturer_id', 'manufacturer_name',
			'manufacturer_username', 'manufacturer_company', 'manufacturer_code'
		);

		preg_match_all('#%(.*?)%#i', strtolower($path), $matches, PREG_SET_ORDER);

		$usedCodes    = ArrayHelper::getColumn($matches, 1);
		$usedFlatCols = array_intersect($flatCols, $usedCodes);

		$loader = new ProductsCacheReader;

		$loader->filterValue('product_id', $productId);
		$loader->filterValue('variant_id', $variantId);

		if ($sellerUid)
		{
			$loader->filterValue('seller_uid', $sellerUid);
		}

		$it   = $loader->getIterator();
		$item = $it->current();

		if (count($usedFlatCols))
		{
			foreach ($usedFlatCols as $key)
			{
				$path = str_ireplace('%' . $key . '%', isset($item, $item->$key) ? $item->$key : '', $path);
			}
		}

		// Try to support spec fields, this is slow - we'd implement speedy later
		$usedOthers     = array_diff($usedCodes, $flatCols);
		$specifications = isset($item, $item->specifications) ? json_decode($item->specifications, true) : array();

		foreach ($usedOthers as $rKey)
		{
			if (substr($rKey, 0, 5) == 'spec_' && is_numeric($fid = str_replace('spec_', '', $rKey)))
			{
				$value = ArrayHelper::getValue($specifications, 'f' . $fid);

				$path  = str_ireplace('%' . $rKey . '%', is_scalar($value) ? $value : '', $path);
			}
		}

		// Sure? if (strtoupper(JFile::stripExt(basename($path))) != '%RANDOM%')
		$path = str_ireplace('%RANDOM%', '*', $path);

		return $path;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $product_id  Product id of the item
	 * @param   int   $variant_id  Variant id of the item if it is not the main product
	 * @param   bool  $blank       Whether to return a blank (placeholder) image in case no matching images are found.
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImages($product_id, $variant_id = null, $blank = true)
	{
		if ($variant_id)
		{
			$images = $this->helper->media->getImages('variants', $variant_id, false, false);
		}

		if (empty($images))
		{
			$images = $this->helper->media->getImages('products', $product_id, false, false);
		}

		if (!$variant_id)
		{
			$primary = $this->helper->media->getImage('products.primary_image', $product_id, false, false);

			if ($primary)
			{
				array_unshift($images, $primary);
			}
		}

		$pFiles = $this->helper->media->getFilesFromPattern('products', 'images', array($this, 'replaceCode'), array($product_id, $variant_id, 0));
		$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

		if ($images)
		{
			foreach ($images as &$image)
			{
				$image = $this->helper->media->getURL($image);
			}
		}
		elseif ($blank)
		{
			$images[] = $this->helper->media->getBlankImage(true);
		}

		return $images;
	}

	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $product_id  Product id of the item
	 * @param   int   $variant_id  Variant id of the item if it is not the main product
	 * @param   bool  $blank       Whether to return a blank (placeholder) image in case no matching images are found.
	 * @param   bool  $url         Whether to convert the paths into urls routes.
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImage($product_id, $variant_id = null, $blank = true, $url = true)
	{
		if ($variant_id)
		{
			$image = $this->helper->media->getImage('variants', $variant_id, false, $url);
		}
		else
		{
			$image = $this->helper->media->getImage('products.primary_image', $product_id, false, $url);
		}

		if (empty($image))
		{
			$image = $this->helper->media->getImage('products', $product_id, false, $url);
		}

		if (empty($image))
		{
			$pFiles = $this->helper->media->getFilesFromPattern('products', 'images', array($this, 'replaceCode'), array($product_id, $variant_id, 0));

			if (isset($pFiles[0]->path))
			{
				$image = $this->helper->media->getURL($pFiles[0]->path);
			}
		}

		if ($blank && strlen($image) == 0)
		{
			$image = $this->helper->media->getBlankImage(true);
		}

		return $image;
	}

	/**
	 * Get the fields for a selected product
	 *
	 * @param  int    $product_id  Product id in concern
	 * @param  array  $types       Type of fields viz 'core' or 'variant' or both to be loaded     *
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getFields($product_id, $types = array('core', 'variant'))
	{
		$categories = $this->getCategories($product_id);
		$field_ids  = $this->helper->category->getFields($categories, $types, true, 'product');
		$filter     = array(
			'id'          => $field_ids,
			'state'       => 1,
			'list.select' => array(
				'a.id, a.title, a.type, a.context, a.params, a.parent_id',
				$this->db->qn('c.title', 'group'),
			),
		);

		return $this->helper->field->loadObjectList($filter);
	}

	/**
	 * Update stock for a product for the given seller
	 *
	 * @param   int  $productId
	 * @param   int  $sellerUid
	 * @param   int  $stock
	 * @param   int  $overStock
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setStock($productId, $sellerUid, $stock = null, $overStock = null)
	{
		$table = $this->getTable('ProductSeller');
		$keys  = array('product_id' => $productId, 'seller_uid' => $sellerUid);

		$table->load($keys);

		if (!$table->get('id'))
		{
			$table->bind($keys);
			$table->set('state', 1);
		}

		// Category must have been saved already otherwise this will break
		list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($productId, $sellerUid);

		if ($hStock)
		{
			// Its ok, we have the value from input to be saved
		}
		elseif ($table->get('id'))
		{
			// If super stock management, do not change existing stock
			return true;
		}
		else
		{
			$stock     = $dStock;
			$overStock = $doStock;
		}

		$table->set('stock', $stock);
		$table->set('over_stock', $overStock);

		return $table->store();
	}

	/**
	 * Get stock for a product for the given seller
	 *
	 * @param   int  $productId
	 * @param   int  $variantId
	 * @param   int  $sellerUid
	 *
	 * @return  stdClass
	 *
	 * @since   2.0.0
	 */
	public function getStock($productId, $variantId, $sellerUid)
	{
		if ($variantId)
		{
			$keys = array(
				'list.select' => 'a.stock, a.stock_capacity, a.stock_sold, a.stock_reserved',
				'list.from'   => '#__sellacious_variant_sellers',
				'product_id'  => $variantId,
				'seller_uid'  => $sellerUid,
			);
		}
		else
		{
			$keys = array(
				'list.select' => 'a.stock, a.stock_capacity, a.stock_sold, a.stock_reserved',
				'list.from'   => '#__sellacious_product_sellers',
				'product_id'  => $productId,
				'seller_uid'  => $sellerUid,
			);
		}

		$obj  = $this->loadObject($keys);

		return $obj;
	}

	/**
	 * Update price for a product for the given seller
	 *
	 * @param   int  $product_id
	 * @param   int  $seller_uid
	 * @param   int  $price_ovr
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function setPrice($product_id, $seller_uid, $price_ovr)
	{
		$table = $this->getTable('ProductPrices');
		$keys  = array('product_id' => $product_id, 'seller_uid' => $seller_uid, 'is_fallback' => 1);

		$table->load($keys);

		if ($table->get('id') == 0)
		{
			$table->bind($keys);
			$table->set('state', 1);
		}
		elseif (abs($table->get('product_price') - $price_ovr) < 0.01)
		{
			// If its not modified then exit early...
			return true;
		}

		$table->set('ovr_price', $price_ovr);

		// Detect removal of override price, and if so restore the calculated_price as final, else override
		$table->set('product_price', ($price_ovr < 0.01) ? $table->get('calculated_price') : $price_ovr);

		return $table->store();
	}

	/**
	 * Set the return and exchange settings to the product item
	 *
	 * @param   stdClass  $item
	 * @param   bool      $ignore_global  Whether to hide this info if coming from global
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public function setReturnExchange($item, $ignore_global = false)
	{
		if ($item->type != 'physical')
		{
			$item->return_days   = 0;
			$item->return_icon   = '';
			$item->return_tnc    = '';

			$item->exchange_days = 0;
			$item->exchange_icon = '';
			$item->exchange_tnc  = '';

			return;
		}

		$allow_return   = $this->helper->config->get('purchase_return', 0);
		$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

		switch ($allow_return)
		{
			case 2:
				if ($item->return_days > 0)
				{
					$fk                  = array(
						'table_name'    => 'config',
						'context'       => 'purchase_return_icon',
						'record_id'     => 2,
						'doc_reference' => $item->return_days,
					);
					$item->return_icon = $this->helper->media->getFieldValue($fk, 'path');

					if ($item->return_tnc == '')
					{
						$item->return_tnc = $this->helper->config->get('purchase_return_tnc');
					}
				}
				break;
			case 1:
				if ($ignore_global)
				{
					$item->return_days = 0;
					$item->return_icon = '';
					$item->return_tnc  = '';
				}
				else
				{
					$fk     = array(
						'table_name' => 'config',
						'context'    => 'purchase_return_icon',
						'record_id'  => 1,
					);
					$return = $this->helper->media->getItem($fk);

					$item->return_days = (int) $return->doc_reference;
					$item->return_icon = $return->path;
					$item->return_tnc  = $this->helper->config->get('purchase_return_tnc');
				}
				break;
			default:
				$item->return_days = 0;
				$item->return_tnc  = '';
				$item->return_icon = '';
		}

		switch ($allow_exchange)
		{
			case 2:
				if ($item->exchange_days > 0)
				{
					$fk                  = array(
						'table_name'    => 'config',
						'context'       => 'purchase_return_icon',
						'record_id'     => 2,
						'doc_reference' => $item->exchange_days,
					);
					$item->exchange_icon = $this->helper->media->getFieldValue($fk, 'path');

					if ($item->exchange_tnc == '')
					{
						$item->exchange_tnc = $this->helper->config->get('purchase_exchange_tnc');
					}
				}
				break;
			case 1:
				if ($ignore_global)
				{
					$item->exchange_days = 0;
					$item->exchange_icon = '';
					$item->exchange_tnc  = '';
				}
				else
				{
					$fk       = array(
						'table_name' => 'config',
						'context'    => 'purchase_exchange_icon',
						'record_id'  => 1,
					);
					$exchange = $this->helper->media->getItem($fk);

					$item->exchange_days = (int) $exchange->doc_reference;
					$item->exchange_icon = $exchange->path;
					$item->exchange_tnc  = $this->helper->config->get('purchase_exchange_tnc');
				}
				break;
			default:
				$item->exchange_days = 0;
				$item->exchange_icon = '';
				$item->exchange_tnc  = '';
		}
	}

	/**
	 * Get single valued code for a given product, variant and seller combination.
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  string  The item code
	 *
	 * @since   1.4.0
	 */
	public function getCode($product_id, $variant_id, $seller_uid)
	{
		// We allow no variant selection
		$pattern = $variant_id === '' ? 'P%dV%sS%d' : 'P%dV%dS%d';

		return sprintf($pattern, $product_id, $variant_id, $seller_uid);
	}

	/**
	 * Parse the single valued code for a given product, variant and seller combination and extract these fields.
	 *
	 * @param   string  $code
	 * @param   int     $product_id
	 * @param   int     $variant_id
	 * @param   int     $seller_uid
	 *
	 * @return  bool  False, if the pattern does not match. True otherwise
	 *
	 * @since   1.4.0
	 */
	public function parseCode($code, &$product_id = null, &$variant_id = null, &$seller_uid = null)
	{
		// Regex is not wrapped inside ^ and $ intentionally.
		if (preg_match('/P([\d]+)V([\d]*)S(-1|[\d]+)/i', strtoupper($code), $matches))
		{
			list (, $product_id, $variant_id, $seller_uid) = $matches;

			// At least the product id must be greater than zero
			return $product_id > 0;
		}

		return false;
	}

	/**
	 * Whether the given product can be compared against other product/variants
	 *
	 * @param   int  $product_id  Product Id
	 *
	 * @return  bool
	 *
	 * @since   1.4.0
	 */
	public function isComparable($product_id)
	{
		$result = $this->helper->config->get('product_compare');

		if (!$result)
		{
			return false;
		}

		try
		{
			$categories = $this->getCategories($product_id);

			if (count($categories) == 0)
			{
				return true;
			}
		}
		catch (Exception $e)
		{
			return true;
		}

		try
		{
			$query = $this->db->getQuery(true);
			$query->select('c.id, b.compare')
				->from('#__sellacious_categories AS c')
				->join('inner', '#__sellacious_categories AS b ON b.lft <= c.lft && c.rgt <= b.rgt')
				->where('c.id = ' . implode(' OR c.id = ', array_map('intval', $categories)))
				->order(('c.id ASC, b.lft DESC'));

			$objects = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return false;
		}

		$default = true;
		$ok      = true;
		$prev    = 0;

		foreach ($objects as $object)
		{
			if ($ok || $prev != $object->id)
			{
				// Reset iteration variable to awesome
				$ok   = true;
				$prev = $object->id;

				if ($object->compare == 1)
				{
					// If enabled, announce eureka!
					return true;
				}
				elseif ($object->compare == -1)
				{
					// If disabled, ditch this castle and now we default to false
					$ok      = false;
					$default = false;
				}
			}
		}

		// If at the last iteration we had awesome, then assume default 'yes', else it is a 'no'
		return $default;
	}

	/**
	 * Method to watermark e-product images with configured watermark overlay
	 *
	 * @param   int  $product_id
	 *
	 * @return  void
	 *
	 * @since   1.3.1
	 *
	 * @throws  Exception
	 *
	 * @deprecated
	 */
	public function watermark($product_id)
	{
		$filter    = array('table_name' => 'products', 'context' => 'eproduct', 'record_id' => $product_id);
		$eProducts = $this->helper->media->loadObjectList($filter);
		$eProducts = array_filter($eProducts, function ($eproduct) {
			$helper = SellaciousHelper::getInstance();

			return $helper->media->isImage(JPATH_ROOT . '/' . $eproduct->path);
		});

		list($watermark) = $this->helper->media->getImages('config.eproduct_image_watermark', 1, false, false);

		if (empty($eProducts) || !$watermark)
		{
			return;
		}

		foreach ($eProducts as $eproduct)
		{
			if (is_file(JPATH_SITE . '/' . $eproduct->path))
			{
				$folder   = $this->helper->media->getBaseDir('products/eproduct_sample/' . $product_id);
				$filename = ltrim($folder, '/\\ ') . '/' . sha1($eproduct->path) . '-' . $product_id . '.jpg';

				if (!is_file(JPATH_ROOT . '/' . $filename))
				{
					if ($this->helper->media->watermark($eproduct->path, $watermark, null, $filename))
					{
						$data = array(
							'table_name'    => 'products',
							'context'       => 'eproduct_sample',
							'record_id'     => $product_id,
							'path'          => $filename,
							'original_name' => '[w] ' . $eproduct->original_name,
							'type'          => 'image/jpeg',
							'state'         => 1,
						);

						$table = $this->getTable('Media');
						$table->save($data);
					}
				}
			}
		}
	}

	/**
	 * Calculate estimated shipping cost per unit for the given product
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function getShippingDimensions($product_id, $variant_id, $seller_uid)
	{
		func_get_args();

		$table  = $this->getTable('PhysicalSeller');
		$filter = array(
			'list.select' => 'a.id',
			'list.from'   => '#__sellacious_product_sellers',
			'product_id'  => $product_id,
			'seller_uid'  => $seller_uid,
		);

		$psx_id = $this->helper->product->loadResult($filter);

		$table->load(array('psx_id' => $psx_id));

		$registry = new Joomla\Registry\Registry($table->getProperties());

		$length    = $table->get('length');
		$width     = $table->get('width');
		$height    = $table->get('height');
		$weight    = $table->get('weight');
		$volWeight = $table->get('vol_weight');

		$physicalDim = $this->getTable('ProductPhysical');
		$physicalDim->load(array('product_id' => $product_id));

		// If weight not found in seller dimension, get from physical dimension
		if ($registry->get('weight.m') < 0.01)
		{
			$weight = $physicalDim->get('weight');
		}

		if ($registry->get('length.m') < 0.01 ||
		    $registry->get('width.m') < 0.01 ||
		    $registry->get('height.m') < 0.01)
		{
			$length    = $physicalDim->get('length');
			$width     = $physicalDim->get('width');
			$height    = $physicalDim->get('height');
			$volWeight = $physicalDim->get('volWeight');
		}

		$dim = new stdClass;

		$dim->length     = $this->helper->unit->explain($length);
		$dim->width      = $this->helper->unit->explain($width);
		$dim->height     = $this->helper->unit->explain($height);
		$dim->weight     = $this->helper->unit->explain($weight);
		$dim->vol_weight = $this->helper->unit->explain($volWeight);

		return $dim;
	}

	/**
	 * Create a placeholder record for the e-product
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $state
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 *
	 * @deprecated   We do not create draft eproductmedia now
	 */
	public function createEProductMedia($product_id, $variant_id, $seller_uid, $state = 1)
	{
		$table = $this->getTable('EProductMedia');

		$table->set('product_id', $product_id);
		$table->set('variant_id', $variant_id);
		$table->set('seller_uid', $seller_uid);
		$table->set('state', $state);

		$table->check();
		$table->store();

		$media = (object) $table->getProperties();

		$media->media  = null;
		$media->sample = null;

		return $media;
	}

	/**
	 * Get the list of e-product media records and its referenced media files for the selected product_id / variant_id / seller_uid.
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $state
	 * @param   int  $is_latest
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function getEProductMedia($product_id, $variant_id, $seller_uid, $state = null, $is_latest = null)
	{
		$filter = array(
			'list.from'  => '#__sellacious_eproduct_media',
			'product_id' => $product_id,
			'variant_id' => $variant_id,
			'seller_uid' => $seller_uid,
			'list.order' => 'is_latest DESC'
		);

		if (isset($state))
		{
			$filter['state'] = (int) $state;
		}

		if (isset($is_latest))
		{
			$filter['is_latest'] = (int) $is_latest;
		}

		$items = $this->loadObjectList($filter);

		if (is_array($items))
		{
			$filter = array(
				'list.select' => 'a.id, a.path, a.state, a.original_name',
				'table_name'  => 'eproduct_media',
				'context'     => null,
				'record_id'   => null,
			);

			if (isset($state))
			{
				$filter['state'] = $state;
			}

			foreach ($items as &$item)
			{
				$filter['record_id'] = $item->id;

				$filter['context'] = 'media';
				$item->media       = $this->helper->media->loadObject($filter);

				$filter['context'] = 'sample';
				$item->sample      = $this->helper->media->loadObject($filter);
			}
		}
		else
		{
			$items = array();
		}

		return $items;
	}

	/**
	 * Get the product basic attribute that are specific to the product type but not dependent on the seller/variant
	 *
	 * @param   int     $product_id
	 * @param   string  $type
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function getAttributesByType($product_id, $type)
	{
		$result = new stdClass;

		if ($type == 'physical')
		{
			$table  = $this->getTable('ProductPhysical');
			$table->load(array('product_id' => $product_id));

			$result = (object) $table->getProperties();
		}
		elseif ($type == 'electronic')
		{
			// Nothing yet.
		}
		elseif ($type == 'package')
		{
			// Nothing yet.
		}

		return $result;
	}

	/**
	 * Get the product seller attribute that are specific to the product type for the given seller
	 *
	 * @param   int     $product_id
	 * @param   int     $seller_uid
	 * @param   string  $type
	 *
	 * @return  stdClass
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getSellerAttributesByType($product_id, $seller_uid, $type)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		/** @var  \SellaciousTableProductSeller  $table */
		$table = $this->getTable('ProductSeller');
		$query->select('a.*')
			->from($db->qn($table->getTableName(), 'a'))
			->where('a.product_id = ' . (int) $product_id)
			->where('a.seller_uid = ' . (int) $seller_uid);

		if ($type == 'physical')
		{
			$query->select('psp.*')
				->join('left', $db->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = a.id');
		}
		elseif ($type == 'electronic')
		{
			$query->select('pse.*')
				->join('left', $db->qn('#__sellacious_eproduct_sellers', 'pse') . ' ON pse.psx_id = a.id');
		}
		elseif ($type == 'package')
		{
			$query->select('psk.*')
				->join('left', $db->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = a.id');
		}
		else
		{
			return new stdClass;
		}

		$query->select('u.name, u.username, u.email')
			->join('inner', '#__users u ON u.id = a.seller_uid');

		$query->select('s.category_id, s.title AS company, s.code AS seller_code')
			->join('inner', '#__sellacious_sellers s ON u.id = s.user_id');

		$query->select('r.mobile')
			->join('left', '#__sellacious_profiles r ON r.user_id = a.seller_uid');

		try
		{
			$seller = $db->setQuery($query)->loadObject();

			if ($seller)
			{
				$table->parseJson($seller);
			}
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLER_FAILED', $e->getMessage()));
		}

		if (!$seller)
		{
			$seller = (object) $table->getProperties();

			$seller->name        = '';
			$seller->username    = '';
			$seller->email       = '';
			$seller->mobile      = '';
			$seller->category_id = '';
			$seller->company     = '';
			$seller->seller_code = '';
		}

		return $seller;
	}

	/**
	 * Set the product basic attribute that are specific to the product type but not dependent on the seller/variant
	 *
	 * @param   array   $attributes
	 * @param   int     $product_id
	 * @param   string  $type
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function setAttributesByType($attributes, $product_id, $type)
	{
		if ($type == 'physical')
		{
			$table = $this->getTable('ProductPhysical');

			$table->load(array('product_id' => $product_id));

			$table->bind($attributes);
			$table->set('product_id', $product_id);

			$table->check();
			$table->store();
		}
		elseif ($type == 'electronic')
		{
			// Nothing yet.
		}
		elseif ($type == 'package')
		{
			$codes = ArrayHelper::getValue($attributes, 'products', '', 'string');
			$codes = explode(',', $codes);

			foreach ($codes as $i => $code)
			{
				$codes[$i] = $this->helper->product->parseCode($code, $pid, $vid) ? array('product_id' => $pid, 'variant_id' => $vid) : null;
			}

			$codes = array_filter($codes);

			$this->helper->package->setProducts($product_id, $codes);

			// Dimension attributes to save yet, see if they are required.
		}

		return;
	}

	/**
	 * Set the product seller attribute that are specific to the product type for the given seller
	 *
	 * @param   array   $attribs
	 * @param   int     $product_id
	 * @param   int     $seller_uid
	 * @param   string  $type
	 *
	 * @return  int  The PSX_ID, viz. product seller x-reference key
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function setSellerAttributesByType($attribs, $product_id, $seller_uid, $type)
	{
		// Todo: Create product type classes extended from base product class to handle each type of products
		// Extract the common properties for common table
		$table  = $this->getTable('ProductSeller');
		$table->load(array('product_id' => $product_id, 'seller_uid' => $seller_uid));

		$disableStock = ArrayHelper::getValue($attribs, 'disable_stock', null);

		// Category must have been saved already otherwise this will break
		list($hStock, $dStock, $doStock) = $this->helper->product->getStockHandling($product_id, $seller_uid);

		if ($hStock || (!is_null($disableStock) && $disableStock == 0))
		{
			// Its ok, we have the value from input to be saved
		}
		elseif ($table->get('id'))
		{
			// If super stock management, do not change existing stock
			$attribs['stock']      = null;
			$attribs['over_stock'] = null;
		}
		else
		{
			$attribs['stock']      = $dStock;
			$attribs['over_stock'] = $doStock;
		}

		unset($attribs['id']);

		// We need to modify array in loop
		$values = $attribs;

		foreach ($values as $key => $value)
		{
			if (property_exists($table, $key))
			{
				$table->set($key, is_scalar($value) ? $value : json_encode($value));

				// Remove common attributes from main array
				unset($attribs[$key]);
			}
		}

		$table->set('product_id', $product_id);
		$table->set('seller_uid', $seller_uid);

		$table->check();
		$table->store();

		if (!($psx_id = $table->get('id')))
		{
			throw new Exception($table->getError());
		}

		// Now save type specific seller attributes
		switch ($type)
		{
			case 'physical':
				$table = $this->getTable('PhysicalSeller');
				$table->load(array('psx_id' => $psx_id));

				$allow_return   = $this->helper->config->get('purchase_return', 0);
				$allow_exchange = $this->helper->config->get('purchase_exchange', 0);

				if ($allow_return != 2)
				{
					$attribs['return_days'] = 0;
					$attribs['return_tnc']  = '';
				}

				if ($allow_exchange != 2)
				{
					$attribs['exchange_days'] = 0;
					$attribs['exchange_tnc']  = '';
				}

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;

			case 'package':
				$table = $this->getTable('PackageSeller');
				$table->load(array('psx_id' => $psx_id));

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;

			case 'electronic':
				$eproducts = ArrayHelper::getValue($attribs, 'eproduct', array(), 'array');
				unset($attribs['eproduct']);

				if (is_array($eproducts))
				{
					$this->saveEProductMedia($eproducts);
				}

				$table = $this->getTable('EProductSeller');
				$table->load(array('psx_id' => $psx_id));

				$table->bind($attribs);
				$table->set('psx_id', $psx_id);
				$table->check();
				$table->store();
				break;
		}

		return $psx_id;
	}

	/**
	 * Save the media information (actual media has already been uploaded and assigned)
	 *
	 * @param   array  $eproducts
	 *
	 * @return  bool
	 *
	 * @since   1.4.0
	 */
	protected function saveEProductMedia(array $eproducts)
	{
		foreach ($eproducts as $eproduct)
		{
			// Product Id is already bound to each of these as the rows are created beforehand.
			// Maybe we should unset those to prevent changes?
			$table = $this->getTable('EProductMedia');

			$eproduct['is_latest'] = isset($eproduct['is_latest']) ? $eproduct['is_latest'] : 0;
			$eproduct['state']     = isset($eproduct['state']) ? $eproduct['state'] : 0;
			$eproduct['hotlink']   = isset($eproduct['hotlink']) ? $eproduct['hotlink'] : 0;

			$table->load($eproduct['id']);
			$table->bind($eproduct);
			$table->check();
			$table->store();

			// Mark related media as protected to prevent direct downloads
			$this->helper->media->protect('eproduct_media', $eproduct['id'], true);
		}

		return true;
	}

	/**
	 * Get the applicable stock handling for this product
	 *
	 * @param   int  $productId
	 * @param   int  $sellerUid
	 *
	 * @return  array  An ordered array [bool $allow, int $stock, int $overStock]
	 *
	 * @since   1.5.2
	 */
	public function getStockHandling($productId = null, $sellerUid = null)
	{
		static $cache = array();

		$keyP = sprintf('%d:%d', $productId, 0);
		$keyS = sprintf('%d:%d', $productId, $sellerUid);

		if (!isset($cache[$keyP]))
		{
			$allow     = null;
			$stock     = null;
			$overStock = null;
			$handling  = $this->helper->config->get('stock_management', 'product');

			if ($handling == 'global')
			{
				$allow = null;
			}
			elseif ($handling == 'category')
			{
				try
				{
					$categories = $this->getCategories($productId);

					list($allow, $stock, $overStock) = $this->helper->category->getStockHandling($categories);
				}
				catch (Exception $e)
				{
				}
			}
			else
			{
				$allow = $handling == 'product' ? true : false;
			}

			if ($allow === null)
			{
				$allow     = false;
				$stock     = $this->helper->config->get('stock_default', 1);
				$overStock = $this->helper->config->get('stock_over_default', 0);
			}
			elseif ($allow === true)
			{
				$stock     = $stock ?: $this->helper->config->get('stock_default', 10);
				$overStock = $overStock ?: $this->helper->config->get('stock_over_default', 0);
			}

			$cache[$keyP] = array($allow, $stock, $overStock);
		}

		if (!$sellerUid)
		{
			return $cache[$keyP];
		}

		// If allowed in product level, we need to check the seller's setting
		if (!isset($cache[$keyS]))
		{
			list($allow, $stock, $overStock) = $cache[$keyP];

			if ($allow === true)
			{
				$filters = array(
					'list.select' => 'a.disable_stock',
					'list.from'   => '#__sellacious_product_sellers',
					'product_id'  => $productId,
					'seller_uid'  => $sellerUid,
				);
				$disable = $this->loadResult($filters);

				if ($disable)
				{
					// Random high stock to allow Backward compatibility to other extensions and functions.
					$allow     = false;
					$stock     = 9936854;
					$overStock = 0;
				}
			}

			$cache[$keyS] = array($allow, $stock, $overStock);
		}

		return $cache[$keyS];
	}

	/**
	 * Get the applicable Question form
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 * @param   int  $user_id
	 *
	 * @return  JForm
	 *
	 * @since   1.6.0
	 */
	public function getQuestionForm($product_id, $variant_id, $seller_uid, $user_id = null)
	{
		$user = JFactory::getUser($user_id);

		// Guest questions
		if ($user->guest && !$this->helper->config->get('allow_guest_questions'))
		{
			return null;
		}

		// Get the form
		$form = JForm::getInstance('com_sellacious.question', 'question', array('control' => 'jform'));

		// Author info
		if (!$user->guest)
		{
			$form->removeField('questioner_name');
			$form->removeField('questioner_email');

			if ($this->helper->config->get('hide_questions_captcha_registered'))
			{
				$form->removeField('captcha');
			}
		}
		else
		{
			// Captcha guest
			if ($this->helper->config->get('hide_questions_captcha_guest'))
			{
				$form->removeField('captcha');
			}
		}

		$data          = array();
		$data['p_id']  = $product_id;
		$data['v_id']  = $variant_id;
		$data['s_uid'] = $seller_uid;

		$form->bind($data);

		return $form;
	}

	/**
	 * Get the List of questions replied by respective seller
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getQuestions($product_id, $variant_id, $seller_uid)
	{
		$query = $this->db->getQuery(true);
		$query->select('q.*')
			->from('#__sellacious_product_questions AS q')
			->where('q.product_id = ' . (int) $product_id)
			->where('q.variant_id = ' . (int) $variant_id)
			->where('q.seller_uid = ' . (int) $seller_uid)
			->where('q.state = 1')
			->where('q.answer  <> ' . $this->db->quote(''))
			->where('q.replied_by > 0')
			->order('q.created DESC');

		try
		{
			$questions = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return array();
		}

		foreach ($questions as $question)
		{
			if ($question->replied_by > 0)
			{
				$query = $this->db->getQuery(true);
				$query->select('a.*, u.name, u.username, u.email')
					->from($this->db->quoteName('#__sellacious_sellers') . ' AS a')
					->join('LEFT', '#__users AS u ON a.user_id = u.id');

				$query->where($this->db->quoteName('a.user_id') . ' = ' . (int) $question->replied_by);

				$this->db->setQuery($query);

				$seller           = $this->db->loadObject();
				$question->seller = $seller;
			}
		}

		return $questions;
	}

	/**
	 * Get questions unanswered by a particular seller
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getUnansweredQuestions($sellerId)
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from('#__sellacious_product_questions')
			->where('seller_uid = ' . $sellerId)
			->where('state = 0');

		try
		{
			$questions = $this->db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ALERT);

			return array();
		}

		return $questions;
	}

	/**
	 * Get the product languages
	 *
	 * @param   string  $code  Language Code
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getLanguage($code = '')
	{
		$contentLang = JLanguageHelper::getContentLanguages();
		$languages   = array();

		foreach ($contentLang as $item)
		{
			if (empty($code))
			{
				$languages[$item->lang_code] = $item->title;
			}
			elseif ($code == $item->lang_code)
			{
				$languages[$item->lang_code] = '<img src="' . JUri::root() . 'media/mod_languages/images/'. $item->image . '.gif" alt="'. $item->image . '"> ' . $item->title;
			}
		}

		if ($code && !isset($contentLang[$code]))
		{
			// If language with code doesn't exist
			$languages[$code] = JText::_('COM_SELLACIOUS_OPTION_PRODUCT_LISTING_SELECT_LANGUAGE_ALL');
		}

		return $languages;
	}

	/**
	 * Get the associations.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tableName   The name of the table.
	 * @param   string   $context     The context
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
	 * @param   bool     $includeAll  Whether to Include all(*)
	 *
	 * @return  array  The associated items
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	public function getAssociations($extension, $tableName, $context, $id, $pk = 'id', $aliasField = 'alias', $includeAll = false)
	{
		$multiLanguageAssociations = array();

		// MultiLanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
		$queryKey = implode('|', func_get_args());

		if (!isset($multiLanguageAssociations[$queryKey]))
		{
			$multiLanguageAssociations[$queryKey] = array();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('c2.language'))
				->from($db->quoteName($tableName, 'c'))
				->join('INNER', $db->quoteName('#__sellacious_associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
				->join('INNER', $db->quoteName('#__sellacious_associations', 'a2') . ' ON a.assoc_key = a2.assoc_key')
				->join('INNER', $db->quoteName($tableName, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk));

			// Use alias field ?
			if (!empty($aliasField))
			{
				$query->select(
					$query->concatenate(
						array(
							$db->quoteName('c2.' . $pk),
							$db->quoteName('c2.' . $aliasField),
						),
						':'
					) . ' AS ' . $db->quoteName($pk)
				);
			}
			else
			{
				$query->select($db->quoteName('c2.' . $pk));
			}

			$query->where('c.' . $pk . ' = ' . (int) $id);

			if(!$includeAll)
			{
				$query->where('c2.language != ' . $db->quote('*'));
			}

			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('language');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as $tag => $item)
				{
					$multiLanguageAssociations[$queryKey][$tag] = $item;
				}
			}
		}

		return $multiLanguageAssociations[$queryKey];
	}

	/**
	 * Save the product associations
	 *
	 * @param   int     $id       Product Id
	 * @param   int     $assocId  Associating Product Id
	 * @param   string  $context  Context to identify the association
	 * @param   string  $lang     Language Code
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function saveAssociation($id, $assocId, $context, $lang)
	{
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('id, assoc_key')
			->from('#__sellacious_associations')
			->where('id = ' . $id . ' AND context = ' . $db->q($context));
		$db->setQuery($query);
		$assoc = $db->loadObject();

		if (empty($assoc))
		{
			$data = array();
			$associations = array();

			$product = $this->loadObject(array('id' => $id));

			$associations[$product->language] = $id;
			$associations[$lang] = $assocId;
			$key   = md5(json_encode($associations));

			$data['id'] = $id;
			$data['context'] = $db->quote($context);
			$data['assoc_key'] = $db->quote($key);

			$query = $db->getQuery(true)
				->insert(('#__sellacious_associations'))
				->values(implode(',', $data));

			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$key = $assoc->assoc_key;
		}

		$query = $db->getQuery(true)
			->select('id')
			->from('#__sellacious_associations')
			->where('id = ' . $assocId . ' AND context = ' . $db->q($context));
		$db->setQuery($query);
		$assoc2 = $db->loadObject();

		if (empty($assoc2))
		{
			$data = array();
			$data['id'] = $assocId;
			$data['context'] = $db->quote($context);
			$data['assoc_key'] = $db->quote($key);

			$query = $db->getQuery(true)
				->insert('#__sellacious_associations');

			$query->values(implode(',', $data));

			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Retrieve a list of all products belonging to the rule
	 *
	 * @param   int     $ruleId   Shop Rule id
	 * @param   string  $context  The rule context
	 * @param   int     $classId  Class Id
	 * @param   string  $key      Column name to fetch from products
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	public function getRuleProducts($ruleId, $context = 'shoprule', $classId = 0, $key = '')
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		if (!empty($key))
		{
			$query->select('c.' . $key);
		}
		else
		{
			$query->select('c.product_id, c.assignment');
		}

		$query->from($db->qn('#__sellacious_rule_products', 'c'));
		$query->where('c.rule_id = ' . $db->q($ruleId));

		if (!empty($context))
		{
			$query->where('c.context = ' . $this->db->q($context));
		}

		$query->where('c.class_id = ' . (int) $classId);

		$db->setQuery($query);

		if (!empty($key))
		{
			$products = $db->loadColumn();
		}
		else
		{
			$products = $db->loadObjectList();
		}

		return (array) $products;
	}

	/**
	 * Assign selected products to given rules, un-assign from others
	 *
	 * @param   int           $ruleId      Rule Id
	 * @param   string        $context     Rule context
	 * @param   string|array  $products    Target products, other associations will be removed
	 * @param   int           $classId     Class Id
	 * @param   string|int    $assignment  Assignment
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	public function setRuleProducts($ruleId, $context = 'shoprule', $products = array(), $classId = 0, $assignment = 1)
	{
		if (empty($products) && !in_array($assignment, array(1, -1)))
		{
			return;
		}

		$current  = $this->getRuleProducts($ruleId, $context, $classId, 'product_id');
		$products = (array) $products;

		$remove = array_diff($current, $products);
		$addNew = array_diff($products, $current);

		$this->delRuleProducts($ruleId, $remove, $classId, $context);

		if (in_array($assignment, array(1, -1)))
		{
			$this->addRuleProducts($ruleId, $addNew, $classId, $context, $assignment);
		}
		else
		{
			$query = $this->db->getQuery(true);

			$query->insert('#__sellacious_rule_products')
				->columns(array('context', 'rule_id', 'class_id', 'product_id', 'assignment'));
			$query->values($this->db->q($context) . ', ' . $this->db->q($ruleId) . ', ' . $this->db->q($classId) . ', ' . $this->db->q('') . ', ' . $this->db->q($assignment));

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to add products to a rule, in addition to any existing products
	 *
	 * @param   int           $shopRuleId  Rule id in concern
	 * @param   string|array  $products    Product id/code or array of it to be removed
	 * @param   int           $classId     Class Id
	 * @param   string        $context     Rule context
	 * @param   string|int    $assignment  Assignment
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	protected function addRuleProducts($shopRuleId, $products, $classId, $context = 'shoprule', $assignment = 1)
	{
		$products = (array) $products;

		if (count($products) == 0)
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->insert('#__sellacious_rule_products')
			->columns(array('context', 'rule_id', 'class_id', 'product_id', 'assignment'));

		foreach ($products as $product)
		{
			$filters = array(
				'list.from'  => '#__sellacious_rule_products',
				'context'    => $context,
				'rule_id'    => $shopRuleId,
				'class_id'   => $classId,
				'product_id' => $product,
			);

			if (!$this->count($filters))
			{
				$query->values($this->db->q($context) . ', ' . $db->q($shopRuleId) . ', ' . $db->q($classId) . ', ' . $db->q($product) . ', ' . $db->q($assignment));
			}
		}

		$db->setQuery($query)->execute();
	}

	/**
	 * Method to remove products from a rule
	 *
	 * @param   int     $ruleId    Rule id in concern
	 * @param   array   $products  Products to remove
	 * @param   int     $classId   Class Id
	 * @param   string  $context   Rule context
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.7.1
	 */
	public function delRuleProducts($ruleId, $products, $classId, $context = 'shoprule')
	{
		$products = (array) $products;

		if (count($products) == 0)
		{
			return;
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__sellacious_rule_products')
			->where($this->db->qn('product_id') . ' IN (' . implode(',', $this->db->q($products)) . ')');

		$query->where('context = ' . $this->db->q($context));

		if ($classId > 0)
		{
			$query->where('class_id = ' . (int) $classId);
		}

		if ($ruleId > 0)
		{
			$query->where('rule_id = ' . (int) $ruleId);
		}
		else
		{
			$query->where('rule_id = 0');
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Product attributes from plugins for rendering
	 *
	 * @param   string    $code
	 * @param   int       $productId
	 * @param   int       $variantId
	 * @param   int       $sellerUid
	 * @param   Registry  $params
	 * @param   string    $type
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 *
	 * @deprecated  Use where and for what?
	 */
	public function getRenderedAttributes($code, $productId, $variantId, $sellerUid, $params = null, $type = 'latest')
	{
		$dispatcher = JEventDispatcher::getInstance();

		$product = (object) array('code' => $code, 'id' => $productId, 'variant_id' => $variantId, 'seller_uid' => $sellerUid);

		$product->rendered_attributes = array();

		$dispatcher->trigger('onProcessProducts', array('com_sellacious.products.' . $type, array(&$product), $params ? $params->toArray() : array()));

		return $product->rendered_attributes;
	}

	/**
	 * Method to check if a user is allowed to edit a product in different sections
	 *
	 * @param   int     $productId  Id of the product
	 * @param   string  $check      Option to check either all or own permissions or both
	 * @param   int     $userId     Id of the user for which permissions have to be checked
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function canEdit($productId = null, $check = null, $userId = null)
	{
		if ($check == 'all')
		{
			$actions = array('basic', 'seller', 'shipping', 'related', 'seo');
		}
		elseif ($check == 'own')
		{
			$actions = array('basic.own', 'seller.own', 'shipping.own', 'related.own', 'seo.own');
		}
		else
		{
			$actions = array('basic', 'seller', 'shipping', 'related', 'seo',
				'basic.own', 'seller.own', 'shipping.own', 'related.own', 'seo.own');
		}

		$allowEdit = $this->helper->access->checkAny($actions, 'product.edit.', $productId, 'com_sellacious', $userId);

		return $allowEdit;
	}

	/**
	 * Method to get unique field settings for given categories of the product
	 *
	 * @param   int[]  $categoryIds  Category ids
	 * @param   bool   $cached       Whether to get cached results
	 *
	 * @return  Registry
	 *
	 * @since   2.0.0
	 */
	public function getUniqueFieldSetting($categoryIds, $cached = true)
	{
		static $params;

		if (!$cached || !$params)
		{
			$language = JFactory::getLanguage()->getTag();

			// Default global
			$params = new Registry();
			$field  = $this->helper->config->get('product_unique_field', '');
			$params->set('product_unique_field', $field);
			$params->set('product_unique_field_scope', $this->helper->config->get('product_unique_field_scope', 'global'));

			$title = '';

			// Category setting will be ignored if there is more than one category
			if ($categoryIds && count($categoryIds) == 1)
			{
				$categoryId = reset($categoryIds);
				$catField   = $this->helper->category->getCategoryParam($categoryId, 'product_unique_field', '', true);

				if ($catField)
				{
					$field = $catField;

					$params->set('product_unique_field', $field);
					$params->set('product_unique_field_scope', 'category');
				}
			}

			if (is_numeric($field))
			{
				$customField = $this->helper->field->getItem($field);
				$this->helper->translation->translateValue($customField->id, 'sellacious_fields', 'title', $customField->title, $language);

				$title = $customField->title;
			}
			elseif ($field)
			{
				$title = JText::_('COM_SELLACIOUS_CONFIG_FIELD_PRODUCT_UNIQUE_FIELD_OPTION_' . strtoupper(str_replace('.', '_', $field)));
			}

			$params->set('product_unique_field_title', $title);
		}

		return $params;
	}

	/**
	 * Method to get value of the unique field for the product
	 *
	 * @param   int|string  $field      The unique field
	 * @param   int         $productId  Id of the product
	 * @param   int         $sellerUid  Seller user id
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function getUniqueFieldValue($field, $productId, $sellerUid = null)
	{
		if (!$field || !$productId)
		{
			return null;
		}

		$value = null;

		if (is_numeric($field))
		{
			$value = $this->helper->field->getValue('products', $productId, $field);
		}
		else
		{
			$parts = explode('.', $field);

			// For now, we're only handling seller-specific fields
			if (isset($parts[1]) && $parts[0] == 'seller' && $sellerUid)
			{
				$table = $this->getTable('ProductSeller');
				$table->load(array('product_id' => $productId, 'seller_uid' => $sellerUid));

				$value = $table->get($parts[1]);
			}
			else
			{
				$product = $this->getItem($productId);
				$value   = isset($product->$field) ? $product->$field : null;
			}
		}

		return $value;
	}

	/**
	 * Method to validate if there is any existing product for the unique field (if there is any)
	 *
	 * @param   int[]  $categories  Product categories
	 * @param   mixed  $fieldValue  Value of the unique field
	 * @param   int    $productId   Product id
	 * @param   int    $foundId     If an existing product is found for the unique field, then we'll store in this argument
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function validateUniqueField($categories, $fieldValue, $productId = 0, &$foundId = 0)
	{
		$setting = $this->getUniqueFieldSetting($categories);
		$field   = $setting->get('product_unique_field', '');
		$scope   = $setting->get('product_unique_field_scope', 'global');

		if (!$field || !$fieldValue)
		{
			return;
		}

		// Custom field
		if (is_numeric($field))
		{
			$filters = array(
				'list.select' => 'a.id, a.record_id, a.field_value, f.title as field_title',
				'list.from'   => '#__sellacious_field_values',
				'list.join'   => array(
					array('INNER', $this->db->qn('#__sellacious_fields', 'f') . ' ON f.id = a.field_id')
				),
				'list.where'  => array(
					'a.table_name = ' . $this->db->q('products'),
					'a.field_id = ' . (int)$field,
					'a.field_value = ' . $this->db->q($fieldValue),
					'f.state = 1',
				),
			);

			if ($productId)
			{
				$filters['list.where'][] = 'a.record_id != ' . $productId;
			}

			if ($scope == 'product_category' && $categories)
			{
				$filters['list.join'][]  = array('inner', '#__sellacious_product_categories b ON b.product_id = a.record_id');
				$filters['list.where'][] = 'b.category_id IN (' . implode(',', $categories) . ')';
			}

			$value = $this->helper->field->loadObject($filters);

			if ($value)
			{
				$foundId = $value->record_id;
				throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_SAVE_PRODUCT_FIELD_NOT_UNIQUE', $value->field_title));
			}
		}
		// Core field
		else
		{
			$contexts  = explode('.', $field);
			$tableName = '';
			$idField   = 'id';

			// If field is seller-wise
			if (isset($contexts[1]) && $contexts[0] == 'seller')
			{
				$tableName = 'ProductSeller';
				$field     = $contexts[1];
				$idField   = 'product_id';
			}

			$table = $this->getTable($tableName);

			if (property_exists($table, $field))
			{
				$filters = array(
					'list.from' => $table->getTableName(),
					'list.where' => array(
						'a.' . $field . ' = ' . $this->db->q($fieldValue),
					)
				);

				if ($productId)
				{
					$filters['list.where'][] = 'a.' . $idField . ' != ' . (int)$productId;
				}

				if ($scope == 'product_category' && $categories)
				{
					$productColumn           = ($tableName == 'ProductSeller') ? 'a.product_id' : 'a.id';
					$filters['list.join'][]  = array('inner', '#__sellacious_product_categories b ON b.product_id = ' . $productColumn);
					$filters['list.where'][] = 'b.category_id IN (' . implode(',', $categories) . ')';
				}

				$product = $this->loadObject($filters);

				if ($product && $product->id)
				{
					$foundId    = $product->id;
					$fieldTitle = JText::_('COM_SELLACIOUS_CONFIG_FIELD_PRODUCT_UNIQUE_FIELD_OPTION_' . strtoupper($field));
					throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_SAVE_PRODUCT_FIELD_NOT_UNIQUE', $fieldTitle));
				}
			}
		}
	}

	/**
	 * Method to check whether SKU for the product listing is unique or not
	 *
	 * @param   int  $productId  Id of the product which will excluded to check the sku in other products
	 * @param   int  $sellerUid  Seller user id
	 * @param   int  $sellerSku  Seller SKU
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isSkuUnique($productId, $sellerUid, $sellerSku)
	{
		$filters = array(
			'list.from'  => '#__sellacious_product_sellers',
			'list.where' => array(
				'a.product_id != ' . (int) $productId,
				'a.seller_uid = ' . (int) $sellerUid,
				'a.seller_sku = ' . $this->db->q($sellerSku),
			)
		);
		$listing = $this->helper->product->loadObject($filters);

		return $listing ? false : true;
	}
}
