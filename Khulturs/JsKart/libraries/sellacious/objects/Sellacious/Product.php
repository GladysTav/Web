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
namespace Sellacious;

use Exception;
use InvalidArgumentException;
use JFactory;
use JLog;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use JText;
use Sellacious\Media\Image\Image;
use Sellacious\Media\Image\ImageHelper;
use Sellacious\Price\PriceHelper;
use SellaciousTable;
use stdClass;

defined('_JEXEC') or die;

/**
 * Sellacious Product Object.
 *
 * @note   When accessing this object you need to keep the following processing of output in mind:
 *         * Convert all prices amount to desired currency assuming the seller's currency as the base
 *         * Filter prices on customer category
 *         * Hide out of stock items/sellers
 *         * Sort appropriately if needed
 *
 * Modifying seller id is allowed and various attributes can be fetched using override parameter value.
 * Modifying product id and variant id is NOT supported. Instantiate an new object instead.
 *
 * @since   1.4.0
 */
class Product extends BaseObject
{
	/**
	 * @var  int
	 *
	 * @since   1.4.0
	 */
	protected $product_id;

	/**
	 * @var  int
	 *
	 * @since   1.4.0
	 */
	protected $variant_id;

	/**
	 * @var  int
	 *
	 * @since   1.4.0
	 */
	protected $seller_uid;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $categories;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $images;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $sellers;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $seller_uids;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $prices;

	/**
	 * @var  array
	 *
	 * @since   1.4.0
	 */
	protected $listings;

	/**
	 * Product constructor.
	 *
	 * @param   int  $product_id
	 * @param   int  $variant_id
	 * @param   int  $seller_uid
	 *
	 * @since   1.4.0
	 */
	public function __construct($product_id, $variant_id = null, $seller_uid = null)
	{
		$this->product_id = $product_id;
		$this->variant_id = $variant_id;
		$this->seller_uid = $seller_uid;

		parent::__construct();
	}

	/**
	 * The product id
	 *
	 * @return int
	 *
	 * @since   1.4.0
	 */
	public function getId()
	{
		return $this->product_id;
	}

	/**
	 * The current variant id
	 *
	 * @return int
	 *
	 * @since   1.4.0
	 */
	public function getVariantId()
	{
		return $this->variant_id;
	}

	/**
	 * The current seller uid
	 *
	 * @return int
	 *
	 * @since   1.4.0
	 */
	public function getSellerUid()
	{
		return $this->seller_uid;
	}

	/**
	 * Update the current seller uid
	 *
	 * @param   int  $seller_uid
	 *
	 * @return  static
	 *
	 * @since   1.4.0
	 */
	public function setSellerUid($seller_uid)
	{
		$this->seller_uid = $seller_uid;

		// Identity modified, reset object
		$this->clear();

		return $this;
	}

	/**
	 * Clear all attributes of this object excluding the identifier keys
	 *
	 * @return  static
	 *
	 * @since   1.4.0
	 */
	public function clear()
	{
		$this->attributes  = null;
		$this->categories  = null;
		$this->images      = null;
		$this->sellers     = null;
		$this->seller_uids = null;
		$this->prices      = null;
		$this->listings    = null;

		return $this;
	}

	/**
	 * Load the basic attributes for this product / variant object instance
	 * Todo: Add physical/electronic etc type specific attributes
	 *
	 * @return  void
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.4.0
	 */
	protected function load()
	{
		$product = $this->getProduct();
		$variant = $this->getVariant();

		$this->bind($product);
		$this->bind($variant, 'variant');
	}

	/**
	 * Return all specification attributes of a given product/variant
	 *
	 * @param   bool   $grouped     Return attributes as a flat list or a grouped list
	 * @param   array  $fieldTypes  Whether to use core, variant or both fields
	 * @param   bool   $valueOnly   Return an array of field values only or the entire field object
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getSpecifications($grouped = true, $fieldTypes = array('core', 'variant'), $valueOnly = false)
	{
		if (!isset($this->specifications))
		{
			$categories = $this->getCategories(true);
			$field_ids  = $this->helper->category->getFields($categories, $fieldTypes, false,  'product');
			$language   = JFactory::getLanguage()->getTag();

			$filter = array('list.select' => 'a.*, c.title AS group_title', 'id' => $field_ids);
			$fields = $this->helper->field->loadObjectList($filter);

			// Specification fields of main product should get overwritten by the matching fields in its variant.
			$valuesP = $this->helper->field->getValue('products', $this->product_id);
			$valuesV = $this->helper->field->getValue('variants', $this->variant_id);
			$values  = array_replace($valuesP, $valuesV);
			$groups  = array();

			foreach ($fields as $field)
			{
				$params            = new Registry($field->params);
				$translatedOptions = (array) $params->get('listoptions_translated', array());

				$this->helper->translation->translateValue($field->parent_id, 'sellacious_fields', 'title', $field->group_title, $language);
				$this->helper->translation->translateValue($field->id, 'sellacious_fields', 'title', $field->title, $language);

				$value = ArrayHelper::getValue($values, $field->id);

				if (isset($value))
				{
					$value = $this->helper->field->renderValue($value, $field->type, $field);

					if (!$value)
					{
						continue;
					}

					if ($language != 'en-GB')
					{
						if (is_array($value))
						{
							foreach ($value as &$item)
							{
								if (isset($translatedOptions[$item]->$language) && !empty($translatedOptions[$item]->$language))
								{
									$item = $translatedOptions[$item]->$language;
								}
							}
						}
						elseif (isset($translatedOptions[$value]->$language) && !empty($translatedOptions[$value]->$language))
						{
							$value = $translatedOptions[$value]->$language;
						}
					}

					$field->value = $value;

					if (!isset($groups[$field->parent_id]))
					{
						$array = array(
							'group_id'    => $field->parent_id,
							'group_title' => $field->group_title,
							'fields'      => array(),
						);

						$groups[$field->parent_id] = $array;
					}

					$groups[$field->parent_id]['fields'][$field->id] = $valueOnly ? $field->value : $field;
				}
			}

			$this->specifications = $groups;
		}

		// Early exit if we already have the data requested
		if ($grouped)
		{
			return $this->specifications;
		}

		// Create flat list on demand
		$flat = array();

		foreach ($this->specifications as $group)
		{
			foreach ($group['fields'] as $fieldId => $fieldObj)
			{
				$flat[$fieldId] = $fieldObj;
			}
		}

		return $flat;
	}

	/**
	 * Get single valued code for a given product, variant and seller combination.
	 *
	 * @param   int  $seller_uid  The override seller uid, otherwise the default seller would be uses
	 *
	 * @return  string  The item code
	 *
	 * @since   1.4.0
	 */
	public function getCode($seller_uid = null)
	{
		return $this->helper->product->getCode($this->product_id, $this->variant_id, $seller_uid ?: $this->seller_uid);
	}

	/**
	 * Retrieve a list of all category ids that this product belongs to
	 *
	 * @param   bool  $recursive  Include the inherited categories
	 * @param   bool  $published  Include the published categories only
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getCategories($recursive = false, $published = false)
	{
		$storeId = serialize(array((int) $recursive, (int) $published));

		if (isset($this->categories[$storeId]))
		{
			return $this->categories[$storeId];
		}

		// Build the database query to get the categories for the product.
		$query = $this->dbo->getQuery(true);

		$query->select($recursive ? 'b.id' : 'a.id')
			->from('#__sellacious_product_categories AS map')
			->where('map.product_id = ' . (int) $this->product_id)
			->join('LEFT', '#__sellacious_categories AS a ON a.id = map.category_id');

		if ($published)
		{
			$query->where('a.state = 1');
		}

		// If we want the categories cascading up to the root category node we need a self-join.
		if ($recursive)
		{
			$query->join('LEFT', '#__sellacious_categories AS b ON b.lft <= a.lft AND a.rgt <= b.rgt');

			if ($published)
			{
				$query->where('b.state = 1');
			}
		}

		$this->dbo->setQuery($query);
		$result = $this->dbo->loadColumn();

		// Clean up any NULL or duplicate values, just in case
		$result = ArrayHelper::toInteger($result);

		$this->categories[$storeId] = empty($result) ? array() : array_unique($result);

		return $this->categories[$storeId];
	}

	/**
	 * Get List of images for this product
	 *
	 * @param   bool  $blank  Whether to return a blank (placeholder) image in case no matching images are found.
	 * @param   bool  $url    Whether to convert the paths into url routes
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getImages($blank = true, $url = false)
	{
		$storeId = serialize(array((bool) $blank, (bool) $url));

		if (!isset($this->images[$storeId]))
		{
			if ($this->variant_id)
			{
				$images = $this->helper->media->getImages('variants', $this->variant_id, false, false);
			}

			if (empty($images))
			{
				$images = $this->helper->media->getImages('products', $this->product_id, false, false);
			}

			if (!$this->variant_id)
			{
				$primary = $this->helper->media->getImage('products.primary_image', $this->product_id, false, false);

				if ($primary)
				{
					array_unshift($images, $primary);
				}
			}

			$pFiles = $this->helper->media->getFilesFromPattern('products', 'images', array($this->helper->product, 'replaceCode'), array($this->product_id, $this->variant_id, 0));
			$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

			if ($images)
			{
				if ($url)
				{
					foreach ($images as &$image)
					{
						$image = $this->helper->media->getURL($image);
					}
				}
			}
			elseif ($blank)
			{
				$images[] = $this->helper->media->getBlankImage(true);
			}

			$this->images[$storeId] = $images;
		}

		return $this->images[$storeId];
	}

	/**
	 * Get List of images for this product
	 *
	 * @return  Image[]
	 *
	 * @since   1.7.0
	 */
	public function getProductImages()
	{
		$images = array();

		if ($this->variant_id)
		{
			$images = ImageHelper::getImages('com_sellacious/variants', $this->variant_id, 'images');
		}

		if (count($images) === 0)
		{
			$images = ImageHelper::getImages('com_sellacious/products', $this->product_id, 'images');
		}

		if (!$this->variant_id)
		{
			$image = ImageHelper::getImage('com_sellacious/products', $this->product_id, 'primary_image');

			if ($image)
			{
				array_unshift($images, $image);
			}
		}

		$preParser = array($this->helper->product, 'replaceCode');
		$arguments = array($this->product_id, $this->variant_id, 0);
		$pFiles    = $this->helper->media->getFilesFromPattern('products', 'images', $preParser, $arguments);

		foreach ($pFiles as $file)
		{
			$images[] = new Image($file->path);
		}

		return $images;
	}

	/**
	 * Get seller-specific prices for this product. Please note that all amounts are in seller's currency.
	 *
	 * @param   int  $sellerUid  The concerned seller uid
	 * @param   int  $quantity   The quantity of the item for rule based prices filtering
	 * @param   int  $ccId       The client category for rule based prices filtering
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getPrices($sellerUid, $quantity = null, $ccId = null)
	{
		$sellerUid = $sellerUid ?: $this->seller_uid;

		$me   = JFactory::getUser();
		$cKey = serialize(array((int) $sellerUid, (int) $quantity));
		$ccId = isset($ccId) ? $ccId : $this->helper->client->getCategory($me->id, true);

		if (!isset($this->prices[$cKey]))
		{
			$handler = PriceHelper::getPsxHandler($this->product_id, $sellerUid);
			$items   = $handler->getProductPrices($this->product_id, $this->variant_id, $sellerUid, $quantity, $ccId);

			$this->prices[$cKey] = array_values($items);
		}

		return $this->prices[$cKey];
	}

	/**
	 * Get best available seller-specific price for this product. Please not that all amounts are in seller's currency.
	 *
	 * @param   int  $seller_uid  The concerned seller uid
	 * @param   int  $quantity    The quantity of the item for rule based prices filtering
	 * @param   int  $client_cat  The client category for rule based prices filtering
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getPrice($seller_uid, $quantity = null, $client_cat = null)
	{
		$seller_uid = $seller_uid ?: $this->seller_uid;

		$prices = $this->getPrices($seller_uid, $quantity, $client_cat);

		if (count($prices))
		{
			$price = reset($prices);
		}
		else
		{
			$price = new stdClass;

			$price->product_id       = $this->product_id;
			$price->variant_id       = $this->variant_id;
			$price->seller_uid       = $seller_uid;
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
		}

		return $price;
	}

	/**
	 * Get seller-specific product attributes
	 *
	 * @param   int  $seller_uid  The selected seller UID
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getSellerAttributes($seller_uid)
	{
		if (!$seller_uid)
		{
			return null;
		}

		if (isset($this->sellers[$seller_uid]))
		{
			return $this->sellers[$seller_uid];
		}

		// Todo: Separate this logic someday into type handlers/subclasses
		$query = $this->dbo->getQuery(true);

		$query->select('psx.product_id, psx.seller_uid, psx.pricing_type')
			->select('psx.stock, psx.disable_stock, psx.over_stock, psx.stock + psx.over_stock AS stock_capacity, psx.quantity_min, psx.quantity_max')
			->where('psx.product_id = ' . (int) $this->product_id)
			->where('psx.seller_uid = ' . (int) $seller_uid)
			->from( $this->dbo->qn('#__sellacious_product_sellers', 'psx'));

		if ($this->get('type') == 'electronic')
		{
			$query->select('pse.delivery_mode, pse.download_limit, pse.download_period, pse.license')
				->select('pse.license_on, pse.license_count, pse.preview_mode, pse.preview_url, pse.params')
				->join('LEFT', $this->dbo->qn('#__sellacious_eproduct_sellers', 'pse') . ' ON pse.psx_id = psx.id');
		}
		elseif ($this->get('type') == 'package')
		{
			/*
			psk.length, psk.height, psk.weight, psk.width, psk.vol_weight,
			psk.shipping_city, psk.shipping_country, psk.shipping_district, psk.shipping_state, psk.shipping_zip
			*/
			$query->select('psk.item_condition, psk.listing_type, psk.whats_in_box, psk.flat_shipping, psk.shipping_flat_fee')
				->select('psk.return_days, psk.return_tnc, psk.exchange_days, psk.exchange_tnc')
				->join('LEFT', $this->dbo->qn('#__sellacious_package_sellers', 'psk') . ' ON psk.psx_id = psx.id');
		}
		// Assume physical by default
		else
		{
			/*
			psp.length, psp.height, psp.weight, psp.width, psp.vol_weight,
			psp.shipping_city, psp.shipping_country, psp.shipping_district, psp.shipping_state, psp.shipping_zip
			*/
			$query->select('psp.item_condition, psp.listing_type, psp.whats_in_box, psp.flat_shipping, psp.shipping_flat_fee')
				->select('psp.return_days, psp.return_tnc, psp.exchange_days, psp.exchange_tnc')
				->join('LEFT', $this->dbo->qn('#__sellacious_physical_sellers', 'psp') . ' ON psp.psx_id = psx.id');
		}

		$seller = $this->dbo->setQuery($query)->loadObject();

		if (isset($seller))
		{
			if ($this->get('type') != 'electronic')
			{
				$shipped_by       = $this->helper->config->get('shipped_by');
				$itemisedShipping = $this->helper->config->get('itemised_shipping', \SellaciousHelperShipping::SHIPPING_SELECTION_CART);

				if ($itemisedShipping == \SellaciousHelperShipping::SHIPPING_SELECTION_CART || $itemisedShipping == \SellaciousHelperShipping::SHIPPING_SELECTION_SELLER)
				{
					$seller->flat_shipping     = 0;
					$seller->shipping_flat_fee = 0;
				}
				elseif ($shipped_by != 'seller')
				{
					$flat_shipping = $this->helper->config->get('flat_shipping');

					$seller->flat_shipping     = $flat_shipping;
					$seller->shipping_flat_fee = $flat_shipping ? $this->helper->config->get('shipping_flat_fee') : 0;
				}
			}

			if ($this->variant_id)
			{
				$query = $this->dbo->getQuery(true);

				$query->select('vs.stock, vs.over_stock')
					->from($this->dbo->qn('#__sellacious_variant_sellers', 'vs'))
					->where('vs.variant_id = ' . (int) $this->variant_id)
					->where('vs.seller_uid = ' . (int) $seller_uid);

				$var_stock = $this->dbo->setQuery($query)->loadObject();

				if (empty($var_stock))
				{
					$var_stock = (object) array('stock' => 0, 'over_stock' => 0);
				}

				$seller->stock          = $var_stock->stock;
				$seller->over_stock     = $var_stock->over_stock;
				$seller->stock_capacity = $var_stock->stock + $var_stock->over_stock;
			}
		}
		else
		{
			// Stand in object
			$seller = new stdClass;

			$seller->product_id        = $this->product_id;
			$seller->seller_uid        = $seller_uid;
			$seller->pricing_type      = 'hidden';
			$seller->stock             = 0;
			$seller->disable_stock     = 0;
			$seller->over_stock        = 0;
			$seller->stock_capacity    = 0;
			$seller->listing_type      = null;
			$seller->item_condition    = null;
			$seller->whats_in_box      = '';
			$seller->flat_shipping     = 0;
			$seller->shipping_flat_fee = 0;
			$seller->return_days       = 0;
			$seller->return_tnc        = null;
			$seller->exchange_days     = 0;
			$seller->exchange_tnc      = null;
		}

		return $this->sellers[$seller_uid] = $seller;
	}

	/**
	 * Get a list of all sellers for a given product
	 *
	 * @param   bool  $only_enabled  Whether to load only the active sellers with active listing
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function getSellers($only_enabled = true)
	{
		$storeId = (int) $only_enabled;

		if (isset($this->seller_uids[$storeId]))
		{
			return $this->seller_uids[$storeId];
		}

		$db    = $this->dbo;
		$query = $db->getQuery(true);

		// PSX(+T) applied
		$query->select('DISTINCT a.seller_uid')
			->from($this->dbo->qn('#__sellacious_product_sellers', 'a'))
			->where('a.product_id = ' . $db->q($this->product_id));

		/*
		 * If not multi-seller shop then just limit result to the default seller,
		 * we still perform query to prevent incomplete product from showing up
		 */
		$multiSeller = $this->helper->config->get('multi_seller', 0);

		if (!$multiSeller)
		{
			$default_seller = $this->helper->config->get('default_seller', 0);

			$query->where('a.seller_uid = ' . (int) $default_seller);
		}

		if ($only_enabled)
		{
			$query->where('a.state = 1');
			$query->join('INNER', '#__users u ON u.id = a.seller_uid')->where('u.block = 0');
			$query->join('INNER', '#__sellacious_sellers s ON u.id = a.seller_uid')->where('s.category_id > 0');

			if ($multiSeller && !$this->helper->config->get('free_listing'))
			{
				$query->join('INNER', '#__sellacious_seller_listing l ON l.product_id = a.product_id AND l.seller_uid = a.seller_uid');

				$nullDt = $db->getNullDate();
				$now    = JFactory::getDate()->toSql();

				$query->where('l.category_id = 0')
					->where('l.publish_up != ' . $this->dbo->q($nullDt))
					->where('l.publish_down != ' . $this->dbo->q($nullDt))
					->where('l.publish_up < ' . $this->dbo->q($now))
					->where('l.publish_down > ' . $this->dbo->q($now))
					->where('l.state = 1');
			}
		}

		try
		{
			$s_uid = $db->setQuery($query)->loadColumn();

			$this->seller_uids[$storeId] = array_filter((array) $s_uid);
		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('COM_SELLACIOUS_PRODUCTS_LOAD_SELLERS_FAILED', $e->getMessage()), JLog::WARNING, 'jerror');

			return array();
		}

		return $this->seller_uids[$storeId];
	}

	/**
	 * Method to get a list of all variants of the selected product
	 *
	 * @return  int[]
	 *
	 * @since   1.4.2
	 */
	public function getVariants()
	{
		$filters = array('product_id' => $this->product_id, 'list.select' => 'a.id');
		$vids    = $this->helper->variant->loadColumn($filters);

		$vids[] = 0;

		return $vids;
	}

	/**
	 * Get the basic listing for this product associated with the given seller
	 *
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.4.0
	 */
	public function getBasicListing($seller_uid)
	{
		$listings = $this->getListings($seller_uid, false);

		return reset($listings) ?: null;
	}

	/**
	 * Get all applicable special listing for this product associated with the given seller
	 *
	 * @param   int  $seller_uid
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.4.0
	 */
	public function getSpecialListings($seller_uid)
	{
		return $this->getListings($seller_uid, true);
	}

	/**
	 * Get listings information for this product associated to the given seller
	 *
	 * @param   int   $seller_uid
	 * @param   bool  $special
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.4.0
	 */
	protected function getListings($seller_uid, $special)
	{
		if (!$seller_uid)
		{
			return null;
		}

		$storeId = serialize(array((int) $seller_uid, (bool) $special));

		if ($this->listings[$storeId])
		{
			return $this->listings[$storeId];
		}

		$now    = JFactory::getDate()->toSql();
		$nullDt = $this->dbo->getNullDate();
		$query  = $this->dbo->getQuery(true);

		$query->select('l.subscription_date AS date')
			->select('l.publish_down AS expiry_date')
			->from($this->dbo->qn('#__sellacious_seller_listing', 'l'))
			->where('l.product_id = ' . (int) $this->product_id)
			->where('l.seller_uid = ' . (int) $seller_uid);

		$query->where('l.publish_up != ' . $this->dbo->q($nullDt))
			->where('l.publish_down != ' . $this->dbo->q($nullDt))
			->where('l.publish_up < ' . $this->dbo->q($now))
			->where('l.publish_down > ' . $this->dbo->q($now))
			->where('l.state = 1');

		if ($special)
		{
			// Special category listing
			$query->select('c.id AS catid, c.params, c.lft AS ordering')
				->join('INNER', $this->dbo->qn('#__sellacious_splcategories', 'c') . ' ON c.id = l.category_id')
				->where('c.state = 1');

			$query->group(('l.seller_uid, l.product_id'))
				->order('c.lft ASC');
		}
		else
		{
			// List only items with a valid standard listing subscription
			$query->where('l.category_id = 0');
		}

		$listings = $this->dbo->setQuery($query)->loadObjectList();

		return $this->listings[$storeId] = $listings;
	}

	/**
	 * Select a seller based on the various attributes
	 * Todo: Implement this based on precedence order
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.0
	 */
	public function pickSeller()
	{
		$seller_uids = $this->getSellers(true);
		$d_seller    = $this->helper->config->get('default_seller');

		if (count($seller_uids) == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NO_SELLER_SELLING'));
		}

		// If there is the default seller available do an early exit else choose a random one
		if ($d_seller && in_array($d_seller, $seller_uids))
		{
			return $d_seller;
		}

		return ArrayHelper::getValue($seller_uids, array_rand($seller_uids, 1));
	}

	/**
	 * Get the variant attributes for the current variant spec
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	protected function getVariant()
	{
		if ($this->variant_id)
		{
			$filters = array(
				'id'          => (int) $this->variant_id,
				'product_id'  => (int) $this->product_id,
				'list.select' => 'a.id, a.title, a.local_sku AS sku, a.description, a.features, a.state, a.params',
			);

			$variant = $this->helper->variant->loadAssoc($filters);

			if (!$variant)
			{
				throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PRODUCT_INVALID_VARIANT_SPECIFIED'));
			}
			elseif ($variant['state'] != 1)
			{
				throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PRODUCT_DISABLED_VARIANT_SPECIFIED'));
			}

			if (!empty($variant['params']))
			{
				$variant['params'] = json_decode($variant['params']);
			}
		}
		else
		{
			$variant = array(
				'id'          => null,
				'title'       => null,
				'sku'         => null,
				'description' => null,
				'features'    => null,
				'state'       => null,
				'params'      => null,
			);
		}

		return $variant;
	}

	/**
	 * Get the product attributes for the current product spec
	 *
	 * @param   bool  $enabledOnly  Whether to load only active product
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	protected function getProduct($enabledOnly = true)
	{
		$multiSeller = $this->helper->config->get('multi_seller', 0);

		$filters = array(
			'id'          => (int) $this->product_id,
			'list.select' => array('a.id, a.title, a.type, a.manufacturer_sku, a.manufacturer_id, ' .
				'a.introtext, a.description, a.metakey, a.metadesc, a.primary_video_url, a.state, a.tags, a.params, a.features, ' .
				'm.title AS manufacturer'),
			'list.join'   => array(
				array('left', '#__sellacious_manufacturers m ON m.id = a.manufacturer_id'),
			),
		);

		// If shop is multi-seller, Seller SKU will be used if seller user id is available
		if ($multiSeller && $this->seller_uid > 0)
		{
			$filters['list.join'][]   = array('left', '#__sellacious_product_sellers psx ON psx.product_id = a.id');
			$filters['list.where'][]  = 'psx.seller_uid = ' . (int)$this->seller_uid;
			$filters['list.select'][] = 'psx.seller_sku as local_sku';
		}
		else
		{
			$filters['list.select'][] = 'a.local_sku';
		}

		$product = $this->helper->product->loadAssoc($filters);

		if (!$product)
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PRODUCT_INVALID_PRODUCT_SPECIFIED'));
		}
		elseif ($enabledOnly && $product['state'] != 1)
		{
			throw new InvalidArgumentException(JText::_('COM_SELLACIOUS_PRODUCT_DISABLED_PRODUCT_SPECIFIED'));
		}
		elseif ($product['type'] == 'physical')
		{
			/** @var  SellaciousTable  $table */
			$table   = $this->helper->product->getTable('ProductPhysical');
			$filters = array(
				'list.select' => 'a.length, a.width, a.height, a.weight, a.vol_weight, a.whats_in_box, a.params',
				'list.from'   => '#__sellacious_product_physical',
				'product_id'  => (int) $this->product_id,
			);
			$physical = $this->helper->product->loadObject($filters);

			if ($physical)
			{
				$table->parseJson($physical);

				$paramsP = $physical->params;
				unset($physical->params);

				$params = new Registry($product['params']);
				$params->set('physical', $paramsP);
				$product['params'] = $params->toObject();

				$product = array_merge($product, get_object_vars($physical));
			}
		}

		return $product;
	}

	/**
	 * Check whether the given product is available for selling or not
	 *
	 * @return  string[]  The reasons for which this product is not available.
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.5
	 */
	public function check()
	{
		$errors = array();

		if (!$this->helper->config->get('multi_seller') && $this->seller_uid != $this->helper->config->get('default_seller'))
		{
			$errors[] = JText::_('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_MULTI_SELLER_NOT_DEFAULT');
		}

		if (!$this->helper->config->get('multi_variant') && $this->variant_id != 0)
		{
			$errors[] = JText::_('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_MULTI_VARIANT_NOT_DEFAULT');
		}

		$cType = $this->get('type');

		if ($cType == 'electronic' || $cType == 'physical')
		{
			$allowType = $this->helper->config->get('allowed_product_type');

			if ($allowType != 'both' && $allowType != $cType)
			{
				$errors[] = JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_PRODUCT_TYPE_NOT_ALLOWED', $cType);
			}
		}
		elseif ($cType == 'package')
		{
			if (!$this->helper->config->get('allowed_product_package'))
			{
				$errors[] = JText::_('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_PRODUCT_PACKAGE_NOT_ALLOWED');
			}
		}
		else
		{
			$errors[] = JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_PRODUCT_TYPE_UNKNOWN');
		}

		$allowed_listing_type = (array) $this->helper->config->get('allowed_listing_type');

		// Todo fixme
		if (!in_array($this->get('seller_listing_type'), $allowed_listing_type))
		{
			// $errors[] = JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_PRODUCT_LISTING_TYPE_NOT_ALLOWED');
		}

		if (!$this->getCategories(false, true))
		{
			$errors[] = JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_CATEGORY_DISABLED');
		}

		if (!$this->getSellers(true))
		{
			$errors[] = JText::sprintf('COM_SELLACIOUS_PRODUCT_NOT_AVAILABLE_SELLER_DISABLED');
		}

		return $errors;
	}
}
