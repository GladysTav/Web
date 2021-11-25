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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;
use Sellacious\Price\PriceHelper;
use Sellacious\Product as ProductItem;
use Sellacious\Seller;

/**
 * Sellacious product model
 *
 * @since   1.0.0
 */
class SellaciousModelProduct extends SellaciousModelItem
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$code  = $this->app->input->get('p');
		$valid = $this->helper->product->parseCode($code, $productId, $variantId, $sellerUid);

		if ($valid)
		{
			$this->app->input->set('id', $productId);
			$this->app->input->set('variant_id', $variantId);
			$this->app->input->set('seller_uid', $sellerUid);

			$this->state->set($this->name . '.id', $productId);
			$this->state->set($this->name . '.variant_id', $variantId);
			$this->state->set($this->name . '.seller_uid', $sellerUid);
			$this->state->set($this->name . '.code', $code);
		}

		$config = ConfigHelper::getInstance('com_sellacious');

		$this->state->set($this->name . '.multi_variant', $config->get('multi_variant'));
		$this->state->set($this->name . '.multi_seller', $config->get('multi_seller'));
		$this->state->set($this->name . '.default_seller', $config->get('default_seller'));
	}

	/**
	 * Return a product item
	 *
	 * @param   int  $pk  The id of the primary key.
	 *
	 * @return  stdClass
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{
		static $cache;

		$cache_key = md5(serialize($this->state));

		if (empty($cache[$cache_key]))
		{
			$product_id  = $this->state->get($this->name . '.id', $pk);
			$seller_uid2 = $this->state->get($this->name . '.seller_uid');

			list($variant_id, $seller_uid) = $this->switchItem();

			if (!$seller_uid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NO_SELLER_SELLING'));
			}

			$code = $this->helper->product->getCode($product_id, $variant_id, $seller_uid);

			$this->state->set($this->name . '.variant_id', $variant_id);
			$this->state->set($this->name . '.seller_uid', $seller_uid);
			$this->state->set($this->name . '.code', $code);

			if ($seller_uid2 && $seller_uid2 != $seller_uid)
			{
				JLog::add(JText::_('COM_SELLACIOUS_PRODUCT_SPECIFIED_SELLER_NOT_SELLING_ITEM_SWITCHED'), JLog::NOTICE, 'jerror');
			}

			$product = new ProductItem($product_id, $variant_id, $seller_uid);
			$seller  = new Seller($seller_uid);

			$sellerProp    = $seller->getAttributes();
			$sellerAttribs = $product->getSellerAttributes($seller_uid);

			$product->bind($sellerProp, 'seller');
			$product->bind($sellerAttribs);

			$object = (object) $product->getAttributes();

			$multiVariant    = $this->state->get($this->name . '.multi_variant');
			$variantSeparate = $multiVariant == 2;

			$object->code             = $product->getCode($seller_uid);
			$object->categories       = $product->getCategories(false);
			$object->specifications   = $product->getSpecifications(false);
			$object->images           = $product->getImages(false, true);
			$object->attachments      = $this->helper->product->getAttachments($product_id, $variant_id, $seller_uid);
			$object->special_listings = $product->getSpecialListings($seller_uid);
			$object->seller_rating    = $this->helper->rating->getSellerRating($seller_uid);
			$object->rating           = $this->helper->rating->getProductRating($product_id, ($variantSeparate ? $variant_id : null));

			if ($object->type == 'package')
			{
				$object->package_items = $this->helper->package->getProducts($product_id, true);
			}

			$this->helper->product->setReturnExchange($object);

			$variantIds = $product->getVariants();
			$sellerUids = $product->getSellers();

			$object->sellers   = $this->getItemSellers($sellerUids, $variantIds);
			$object->variants  = $this->getItemVariants($sellerUids);

			// Organize price attributes
			$object->prices    = $product->getPrices($seller_uid);
			$object->price     = (object) $product->getPrice($seller_uid, 1);
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

			$offers = array();
			$taxes  = array();

			foreach ($object->shoprules as &$rule)
			{
				if ($rule->type == 'discount')
				{
					$offers[] = &$rule;
				}
				elseif ($rule->type == 'tax')
				{
					$taxes[] = &$rule;
				}
			}

			$object->offers = $offers;
			$object->taxes  = $taxes;

			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onProcessProduct', array('com_sellacious.product', $object));

			$cache[$cache_key] = $object;
		}

		return $cache[$cache_key];
	}

	/**
	 * Return a product item
	 *
	 * @return  array  An array containing [variantId, sellerUid]
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function switchItem()
	{
		$productId = $this->state->get($this->name . '.id');
		$variantId = $this->state->get($this->name . '.variant_id');
		$sellerUid = $this->state->get($this->name . '.seller_uid');

		// Check if the selected variant is the variant of selected product, else ignore it
		$multiVariant = $this->state->get($this->name . '.multi_variant');
		$filter       = array('list.select' => 'a.id', 'product_id' => $productId, 'state' => 1);
		$variants     = $multiVariant ? $this->helper->variant->loadColumn($filter) : array();

		if (!in_array($variantId, $variants))
		{
			$variantId = 0;
		}

		// Check if the seller selected is valid and listed, else ignore it
		$multiSeller = $this->state->get($this->name . '.multi_seller');
		$defSeller   = $this->state->get($this->name . '.default_seller');
		$records     = $this->helper->product->getSellers($productId, true);
		$sellers     = array();

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
	 * Save the submitted query for the selected product item
	 *
	 * @param   array   $query
	 * @param   string  $code
	 *
	 * @return  int  Record id of the query
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function saveQuery($query, $code)
	{
		// Todo: May be check (in controller) if query is permitted for this seller/product as of global config
		$this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

		$array = array(
			'query'      => $this->helper->productQuery->prepare($query),
			'product_id' => $product_id,
			'variant_id' => $variant_id,
			'seller_uid' => $seller_uid,
		);

		$table = $this->getTable('ProductQuery');
		$table->bind($array);
		$table->check();

		$dispatcher = $this->helper->core->loadPlugins('content');
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.product.query', &$table, true));

		$table->store();

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.product.query', &$table, true));

		return $table->get('id');
	}

	/**
	 * Get the query form for the selected product/variant/seller
	 *
	 * @return  JForm|bool
	 *
	 * @since   1.1.0
	 */
	public function getQueryForm()
	{
		try
		{
			$product_id = (int) $this->getState($this->name . '.id');
			$seller_uid = (int) $this->getState($this->name . '.seller_uid');
			$field_ids  = $this->getQueryFields($product_id, $seller_uid);
			$source     = $this->helper->field->createFormXml($field_ids, 'basic', 'query');

			if (empty($source))
			{
				$form = false;
			}
			else
			{
				$path = JPATH_SELLACIOUS . '/components/com_sellacious/models/fields';
				JFormHelper::addFieldPath($path);

				$name    = strtolower($this->option . '.' . $this->name);
				$options = array('control' => 'jform', 'load_data' => true);
				$form    = JForm::getInstance($name, $source->asXML(), $options, false, false);
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return $form;
	}

	/**
	 * Get the checkout question form for the selected product/variant/seller
	 *
	 * @return  JForm|bool
	 *
	 * @since   2.0.0
	 */
	public function getCheckoutQuestionForm()
	{
		try
		{
			$form = $this->helper->cart->getCheckoutForm(true, 'products', $this->state->get('product.code', ''));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return $form;
	}

	/**
	 * Get list of fields in given categories including hierarchical parents
	 *
	 * @param   int  $product_id  Product Id
	 * @param   int  $seller_uid  Seller's User Id
	 *
	 * @return  int[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	protected function getQueryFields($product_id, $seller_uid)
	{
		static $cache = array();

		if (empty($cache))
		{
			$seller       = $this->helper->product->getSeller($product_id, $seller_uid);
			$queryForm    = is_array($seller->query_form) ? $seller->query_form : array();
			$globalFields = $this->helper->field->getGlobalFields('queryform');

			if ($globalFields)
			{
				foreach ($globalFields as $globalField)
				{
					array_unshift($queryForm, $globalField);
				}
			}

			if (is_array($queryForm) && count($queryForm))
			{
				$cache = $this->helper->field->getListWithGroup($queryForm);
			}
		}

		return $cache;
	}

	/**
	 * Save the asked question for the product item
	 *
	 * @param   array  $data
	 *
	 * @return  int  Record id of the query
	 *
	 * @since   1.6.0
	 */
	public function saveQuestion($data)
	{
		$db = JFactory::getDbo();

		$record                   = new stdClass;
		$record->id               = null;
		$record->product_id       = $data['p_id'];
		$record->variant_id       = $data['v_id'];
		$record->seller_uid       = $data['s_uid'];
		$record->questioner_name  = $data['questioner_name'];
		$record->questioner_email = $data['questioner_email'];
		$record->question         = $data['question'];
		$record->created          = JFactory::getDate()->toSql();
		$record->created_by       = $data['created_by'];

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.question', $record, true));

		$db->insertObject('#__sellacious_product_questions', $record, 'id');

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.question', $record, true));

		return $record->id;
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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

	/**
	 * Get product variants excluding the active variant
	 *
	 * @param   array  $sellerUids
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getItemVariants(array $sellerUids)
	{
		$multiVariant = $this->state->get($this->name . '.multi_variant');

		if (!$multiVariant)
		{
			return array();
		}

		$variantSeparate = $multiVariant == 2;

		$productId = $this->state->get($this->name . '.id');
		$variantId = $this->state->get($this->name . '.variant_id');
		$sellerUid = $this->state->get($this->name . '.seller_uid');

		$records    = array();
		$filter     = array('list.select' => 'a.id', 'product_id' => $productId, 'state' => 1);
		$variantIds = $this->helper->variant->loadColumn($filter);
		$variantIds = array_unique(array_diff(array_merge($variantIds, array(0)), array($variantId)));

		foreach ($variantIds as $varId)
		{
			$vSUid = $this->findVariantSeller($sellerUids, $productId, $varId, $sellerUid);

			if ($vSUid)
			{
				$vProduct = new ProductItem($productId, $varId);
				$oVariant = (object) $vProduct->getAttributes();

				$oVariant->code      = $vProduct->getCode($vSUid);
				$oVariant->images    = $vProduct->getImages(true, true);
				$oVariant->price     = $vProduct->getPrice($vSUid, 1);
				$oVariant->seller    = $vProduct->getSellerAttributes($vSUid);
				$oVariant->rating    = $this->helper->rating->getProductRating($productId, $variantSeparate ? $varId : null);
				$oVariant->shoprules = $this->helper->shopRule->toProduct($oVariant->price, true, true);

				$records[] = $oVariant;
			}
		}

		return $records;
	}

	/**
	 * Get product sellers excluding the active seller
	 *
	 * @param   int[]  $sellerUids
	 * @param   int[]  $variantIds
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function getItemSellers(array $sellerUids, array $variantIds)
	{
		$sellers = array();

		$productId = $this->state->get($this->name . '.id');
		$variantId = $this->state->get($this->name . '.variant_id');
		$sellerUid = $this->state->get($this->name . '.seller_uid');

		$sellerUids = array_diff($sellerUids, array($sellerUid));

		foreach ($sellerUids as $oSellerUid)
		{
			$varId = $this->findSellerVariant($variantIds, $productId, $variantId, $oSellerUid);

			if ($varId !== null)
			{
				$sProduct    = new ProductItem($productId, $varId);
				$sAttributes = $sProduct->getSellerAttributes($oSellerUid);

				// Temporary workaround
				$seller   = new Seller($oSellerUid);
				$registry = new Registry($sAttributes);
				$registry->set('seller', $seller->getAttributes());

				$obj = (object) $registry->flatten('_');

				unset($registry);

				// Product type and product code (not to be confused with seller_type, seller_code)
				$obj->type          = $sProduct->get('type');
				$obj->code          = $sProduct->getCode($oSellerUid);
				$obj->price         = $sProduct->getPrice($oSellerUid, 1);
				$obj->shoprules     = $this->helper->shopRule->toProduct($obj->price, true, true);
				$obj->seller_rating = $this->helper->rating->getSellerRating($oSellerUid);

				$this->helper->product->setReturnExchange($obj, true);

				$sellers[] = $obj;
			}
		}

		return $sellers;
	}
}
