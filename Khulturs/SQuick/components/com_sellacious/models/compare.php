<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;
use Sellacious\Price\PriceHelper;
use Sellacious\Product as ProductObject;
use Sellacious\Seller;

/**
 * Methods supporting a list of Product Categories.
 *
 * @since   1.2.0
 */
class SellaciousModelCompare extends SellaciousModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   12.2
	 */
	protected function populateState()
	{
		$c   = $this->app->input->getString('c');

		$ids = $c ? explode(',', $c) : $this->app->getUserState('com_sellacious.compare.ids', array());

		$this->setState('compare.ids', array_unique($ids));
	}

	/**
	 * Load list of items selected for compare
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.2.0
	 */
	public function getItems()
	{
		static $cache = array();

		$codes = $this->getState('compare.ids');

		if (empty($cache) && is_array($codes))
		{
			$items = array();

			foreach ($codes as $code)
			{
				try
				{
					$items[] = $this->getProduct($code);
				}
				catch (Exception $e)
				{
					JLog::add($e->getMessage(), JLog::INFO, 'jerror');
				}
			}

			$valid = ArrayHelper::getColumn($items, 'code');

			// Update state coz it might contain invalid choices that have been omitted. State value is reusable later.
			$this->state->set('compare.ids', $valid);

			$cache = $items;
		}

		return $cache;
	}

	/**
	 * Get all fields from the products added to comparison
	 *
	 * @return  array
	 *
	 * @since   1.2.0
	 */
	public function getAttributes()
	{
		$groups = array();
		$items  = $this->getItems();

		$specs = ArrayHelper::getColumn($items, 'specifications');
		$specs = array_reduce($specs, 'array_merge', array());
		$specs = ArrayHelper::arrayUnique($specs);
		$specs = array_values($specs);

		foreach ($specs as $field)
		{
			$field     = (array) $field;
			$field_id  = $field['id'];
			$parent_id = $field['parent_id'];

			if (isset($groups[$parent_id]))
			{
				$group = &$groups[$parent_id];
			}
			else
			{
				$group = new stdClass;

				$group->id     = $parent_id;
				$group->title  = $field['group_title'];
				$group->fields = array();

				$groups[$parent_id] = &$group;
			}

			// If the current field is not already processed
			if (!isset($group->fields[$field_id]))
			{
				$group->fields[$field_id] = (object) $field;
			}

			// Reset reference
			unset($group);
		}

		return $groups;
	}

	/**
	 * Get the product for comparison
	 *
	 * @param   string  $code  The product code
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getProduct($code)
	{
		$parsed = $this->helper->product->parseCode($code, $productId, $variantId, $sellerUid2);

		if (!$parsed || !$productId)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_COMPARE_INVALID_PRODUCT_CODE', $code));
		}

		if (!$this->helper->product->isComparable($productId))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_COMPARE_ADD_NOT_ALLOWED'));
		}

		list($variantId, $sellerUid) = $this->switchItem($productId, $variantId, $sellerUid2);

		if (!$sellerUid)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NO_SELLER_SELLING'));
		}

		$product = new ProductObject($productId, $variantId);
		$seller  = new Seller($sellerUid);

		$product->getAttributes();
		$product->bind($seller->getAttributes(), 'seller');
		$product->bind($product->getSellerAttributes($sellerUid));

		$multiVariant    = $this->state->get($this->name . '.multi_variant');
		$variantSeparate = $multiVariant == 2;

		$object = (object) $product->getAttributes();

		$object->code             = $product->getCode($sellerUid);
		$object->categories       = $product->getCategories(false);
		$object->specifications   = $product->getSpecifications(false);
		$object->images           = $product->getImages(true, true);
		$object->attachments      = $this->helper->product->getAttachments($productId, $variantId, $sellerUid);
		$object->special_listings = $product->getSpecialListings($sellerUid);
		$object->seller_rating    = $this->helper->rating->getSellerRating($sellerUid);
		$object->rating           = $this->helper->rating->getProductRating($productId, ($variantSeparate ? $variantId : null));

		$this->helper->product->setReturnExchange($object);

		// Organize price attributes
		$object->prices    = $product->getPrices($sellerUid);
		$object->price     = (object) $product->getPrice($sellerUid);
		$object->shoprules = $this->helper->shopRule->toProduct($object->price, true, true);

		if (abs($object->price->list_price) >= 0.01)
		{
			$object->price->list_price = $object->price->list_price_final;
		}

		foreach ($object->prices as &$alt_price)
		{
			$alt_price->basic_price = $alt_price->sales_price;

			$this->helper->shopRule->toProduct($alt_price);
		}

		// Ignore seller selection as it causes failure in removal of the product from comparison
		$object->shoprules      = $this->helper->shopRule->toProduct($object->price);
		$object->prices         = $product->getPrices($object->seller_uid);
		$object->specifications = $product->getSpecifications(false);

		foreach ($object->prices as &$alt_price)
		{
			$alt_price->basic_price = $alt_price->sales_price;

			$this->helper->shopRule->toProduct($alt_price);
		}

		$this->helper->product->setReturnExchange($object);

		return $object;
	}

	/**
	 * Return a product item
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $variantId  Variant id
	 * @param   int  $sellerUid  Seller user id
	 *
	 * @return  array  An array containing [variantId, sellerUid]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function switchItem($productId, $variantId, $sellerUid)
	{
		$variants     = array();
		$multiVariant = $this->helper->config->get('multi_variant');
		$multiSeller  = $this->helper->config->get('multi_seller');
		$defSeller    = $this->helper->config->get('default_seller');

		if ($multiVariant)
		{
			$filter   = array('list.select' => 'a.id', 'product_id' => $productId, 'state' => 1);
			$variants = $this->helper->variant->loadColumn($filter);
		}

		$variantId = in_array($variantId, $variants) ? $variantId : 0;
		$records   = $this->helper->product->getSellers($productId, true);
		$sellers   = array();

		foreach ($records as $seller)
		{
			if ($multiSeller || $seller->seller_uid == $defSeller)
			{
				$sellers[$seller->seller_uid] = $seller;
			}
		}

		// Find a seller with stock for the selected variant
		$sUid = $this->findVariantSeller(array_keys($sellers), $productId, $variantId, $sellerUid);

		if ($sUid)
		{
			return array($variantId, $sUid);
		}

		if ($variantId == 0 && count($variants))
		{
			// We could not find a seller with stock, we'd try another variant for current seller
			$vId = $this->findSellerVariant($variants, $productId, $variantId, $sellerUid);

			if ($vId !== null)
			{
				return array($vId, $sellerUid);
			}

			// We couldn't find a variant with stock. We'd do over for all other variants x sellers now
			foreach ($variants as $vId)
			{
				$sUid = $this->findVariantSeller(array_keys($sellers), $productId, $vId, $sellerUid);

				return array($vId, $sUid);
			}
		}

		return array($variantId, null);
	}

	/**
	 * Find the variant having stock for a given seller
	 *
	 * @param   int[]  $variants
	 * @param   int    $productId
	 * @param   int    $variantId
	 * @param   int    $sellerUid
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function findSellerVariant(array $variants, $productId, $variantId, $sellerUid)
	{
		$filter = array(
			'list.select' => 'stock + over_stock AS stock_capacity',
			'list.from'   => '#__sellacious_product_sellers',
			'product_id'  => $productId,
			'seller_uid'  => $sellerUid,
		);
		$productStock = $this->helper->product->loadResult($filter);

		// If main variant selected and has stock, nada!
		if ($variantId == 0 && $this->checkStockAndPrice($productId, $variantId, $sellerUid, $productStock))
		{
			return $variantId;
		}

		$filter = array(
			'list.select' => 'variant_id, stock + over_stock AS stock_capacity',
			'list.from'   => '#__sellacious_variant_sellers',
			'seller_uid'  => $sellerUid,
			'variant_id'  => $variants,
		);

		$records = $this->helper->variant->loadObjectList($filter);
		$stocks  = ArrayHelper::getColumn($records, 'stock_capacity', 'variant_id');

		// If variant is selected and selected seller has stock, voila!
		if ($variantId)
		{
			$stock = ArrayHelper::getValue($stocks, $variantId);

			if ($this->checkStockAndPrice($productId, $variantId, $sellerUid, $stock))
			{
				return $variantId;
			}
		}

		// Find a variant with stock, switch!
		foreach ($stocks as $vid => $stock)
		{
			if ($this->checkStockAndPrice($productId, $vid, $sellerUid, $stock))
			{
				return $vid;
			}
		}

		// See if we can fallback to main product
		if ($this->checkStockAndPrice($productId, 0, $sellerUid, $productStock))
		{
			return 0;
		}

		return null;
	}

	/**
	 * Find the variant having stock for a given seller
	 *
	 * @param   int[]  $sellerUids
	 * @param   int    $productId
	 * @param   int    $variantId
	 * @param   int    $sellerUid
	 *
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function findVariantSeller(array $sellerUids, $productId, $variantId, $sellerUid)
	{
		if ($variantId)
		{
			$filter  = array(
				'list.select' => 'seller_uid, stock + over_stock AS stock_capacity',
				'list.from'   => '#__sellacious_variant_sellers',
				'variant_id'  => $variantId,
				'seller_uid'  => $sellerUids,
			);
		}
		else
		{
			$filter  = array(
				'list.select' => 'seller_uid, stock + over_stock AS stock_capacity',
				'list.from'   => '#__sellacious_product_sellers',
				'product_id'  => $productId,
				'seller_uid'  => $sellerUids,
			);
		}

		$records = $this->helper->variant->loadObjectList($filter);
		$stocks  = ArrayHelper::getColumn($records, 'stock_capacity', 'seller_uid');

		// If seller is selected and has stock, voila!
		if (array_key_exists($sellerUid, $stocks))
		{
			$stock = ArrayHelper::getValue($stocks, $sellerUid);

			if ($this->checkStockAndPrice($productId, $variantId, $sellerUid, $stock))
			{
				return $sellerUid;
			}
		}

		// Find a seller who has stock, switch!
		foreach ($records as $uid => $stock)
		{
			if ($uid != $sellerUid && $this->checkStockAndPrice($productId, $variantId, $uid, $stock))
			{
				return $uid;
			}
		}

		return null;
	}

	/**
	 * Check the given product-variant-seller combination for a valid price and stock
	 *
	 * @param   int  $productId
	 * @param   int  $variantId
	 * @param   int  $sellerUid
	 * @param   int  $stock
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function checkStockAndPrice($productId, $variantId, $sellerUid, $stock)
	{
		$config = ConfigHelper::getInstance('com_sellacious');

		if ($config->get('hide_out_of_stock') && $stock <= 0)
		{
			return false;
		}

		if (!$config->get('hide_zero_priced'))
		{
			return true;
		}

		$handler = PriceHelper::getPsxHandler($productId, $sellerUid);

		return $handler->isValidPrice($productId, $variantId, $sellerUid);
	}
}
