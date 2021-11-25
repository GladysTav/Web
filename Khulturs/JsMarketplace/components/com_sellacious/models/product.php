<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
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
		$code  = $this->app->input->get('p');
		$valid = $this->helper->product->parseCode($code, $productId, $variantId, $sellerUid);

		if ($valid)
		{
			$this->app->input->set('id', $productId);
			$this->app->input->set('variant_id', $variantId);
			$this->app->input->set('seller_uid', $sellerUid);
		}

		parent::populateState();

		$multiVariant = $this->helper->config->get('multi_variant');
		$multiSeller  = $this->helper->config->get('multi_seller');
		$defaultSeller = $this->helper->config->get('default_seller');

		if (!$multiVariant)
		{
			$variantId = 0;
		}

		if (!$multiSeller)
		{
			$sellerUid = (int) $this->helper->config->get('default_seller') ?: - 1;
		}

		$newCode = $this->helper->product->getCode($productId, $variantId, $sellerUid);

		$this->state->set($this->name . '.multi_variant', $multiVariant);
		$this->state->set($this->name . '.multi_seller', $multiSeller);
		$this->state->set($this->name . '.default_seller', $defaultSeller);
		$this->state->set($this->name . '.variant_id', $variantId);
		$this->state->set($this->name . '.seller_uid', $sellerUid);
		$this->state->set($this->name . '.code', $newCode);
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

		$dispatcher = $this->helper->core->loadPlugins();
		
		$multiVariant    = $this->state->get($this->name . '.multi_variant');
		$variantSeparate = $multiVariant == 2;

		if (empty($cache[$cache_key]))
		{
			$product_id  = $this->state->get($this->name . '.id', $pk);
			$seller_uid2 = $this->state->get($this->name . '.seller_uid');

			$this->switchItem($pk);

			$variant_id = $this->state->get($this->name . '.variant_id');
			$seller_uid = $this->state->get($this->name . '.seller_uid');

			if (!$seller_uid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NO_SELLER_SELLING'));
			}

			if ($seller_uid2 && $seller_uid2 != $seller_uid)
			{
				JLog::add(JText::_('COM_SELLACIOUS_PRODUCT_SPECIFIED_SELLER_NOT_SELLING_ITEM_SWITCHED'), JLog::NOTICE, 'jerror');
			}

			$product = new ProductItem($product_id, $variant_id);
			$seller  = new Seller($seller_uid);

			$sellerProp    = $seller->getAttributes();
			$sellerAttribs = $product->getSellerAttributes($seller_uid);

			$product->bind($sellerProp, 'seller');
			$product->bind($sellerAttribs);

			$object = (object) $product->getAttributes();

			$object->code             = $product->getCode($seller_uid);
			$object->categories       = $product->getCategories(false);
			$object->specifications   = $product->getSpecifications(false);
			$object->images           = $product->getImages(true, true);
			$object->attachments      = $this->helper->product->getAttachments($product_id, $variant_id, $seller_uid);
			$object->special_listings = $product->getSpecialListings($seller_uid);
			$object->seller_rating    = $this->helper->rating->getSellerRating($seller_uid);
			$object->rating           = $this->helper->rating->getProductRating($product_id, ($variantSeparate ? $variant_id : null));
			$object->sellers          = array();
			$object->variants         = array();

			if ($object->type == 'package')
			{
				$object->package_items = $this->helper->package->getProducts($product_id, true);
			}

			$this->helper->product->setReturnExchange($object);

			// Organize price attributes
			$me    = JFactory::getUser();
			$c_cat = $this->helper->client->getCategory($me->id, true);

			$hideZero   = $this->helper->config->get('hide_zero_priced');
			$hideNoSock = $this->helper->config->get('hide_out_of_stock');

			$object->prices = $product->getPrices($seller_uid, null, $c_cat);
			$price          = (object) $product->getPrice($seller_uid, 1, $c_cat);

			// Todo: Following reference fields to be deprecated - better to create a Price class probably!!
			$object->price_id         = &$price->price_id;
			$object->cost_price       = &$price->cost_price;
			$object->margin           = &$price->margin;
			$object->margin_type      = &$price->margin_type;
			$object->list_price       = &$price->list_price;
			$object->calculated_price = &$price->calculated_price;
			$object->ovr_price        = &$price->ovr_price;
			$object->product_price    = &$price->product_price;
			$object->is_fallback      = &$price->is_fallback;
			$object->client_catid     = &$price->client_catid;
			$object->variant_price    = &$price->variant_price;
			$object->sales_price      = &$price->sales_price;
			$object->basic_price      = &$price->basic_price;
			$object->tax_amount       = &$price->tax_amount;
			$object->discount_amount  = &$price->discount_amount;

			$price->price_display = &$object->price_display;

			$object->price = &$price;

			if (($product->get('stock_capacity') <= 0 && $hideNoSock) || (abs($price->sales_price) < 0.01 && $hideZero))
			{
				throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_NO_SELLER_SELLING'));
			}

			$variantIds = $product->getVariants();
			$sellerUids = $product->getSellers();

			$uids = array_unique(array_merge(array($seller_uid), $sellerUids));

			$sFilter      = array(
				'list.select' => 'stock + over_stock AS stock_capacity',
				'list.from'   => '#__sellacious_product_sellers',
				'product_id'  => $product_id,
				'seller_uid'  => $seller_uid,
			);
			$productStock = $this->helper->product->loadResult($sFilter);

			// Show sellers
			foreach ($uids as $s_uid)
			{
				if ($seller_uid == $s_uid)
				{
					continue;
				}

				foreach ($variantIds as $vId)
				{
					$vId = $this->findSellerVariant(array($vId), $product_id, $variant_id, $s_uid, $productStock);

					if ($vId !== null)
					{
						$sProduct = new ProductItem($product_id, $vId);
						$oVariant = (object) $sProduct->getAttributes();

						$oVariant->price  = $sProduct->getPrice($s_uid, 1, $c_cat);
						$oVariant->seller = $sProduct->getSellerAttributes($s_uid);

						if (($oVariant->seller->stock_capacity > 0 || !$hideNoSock) && (abs($oVariant->price->sales_price) >= 0.01 || !$hideZero))
						{
							$seller_k     = new Seller($s_uid);
							$sellerProp_k = $seller_k->getAttributes();

							// Temporary workaround
							$registry = new Registry($oVariant->seller);
							$registry->set('seller', $sellerProp_k);

							$object_k = (object) $registry->flatten('_');

							unset($registry);

							// Product type and product code (not to be confused with seller_type, seller_code)
							$object_k->type          = $object->type;
							$object_k->code          = $sProduct->getCode($s_uid);
							$object_k->price         = $oVariant->price;
							$object_k->shoprules     = $this->helper->shopRule->toProduct($object_k->price, true, true);
							$object_k->seller_rating = $this->helper->rating->getSellerRating($s_uid);

							$this->helper->product->setReturnExchange($object_k, true);

							$object->sellers[] = $object_k;

							break;
						}
					}
				}
			}

			// Show variants
			if ($multiVariant <> 0)
			{
				foreach ($variantIds as $v_id)
				{
					$variant = $this->helper->variant->getItem($v_id);

					if ($variant_id == $v_id || ($variant->state != '' && $variant->state == 0))
					{
						continue;
					}

					$vProduct = new ProductItem($product_id, $v_id);
					$oVariant = (object) $vProduct->getAttributes();

					$uids = array_unique(array_merge(array($seller_uid), $sellerUids));

					foreach ($uids as $uid)
					{
						$vSUid = $this->findVariantSeller(array($uid), $product_id, $v_id);

						if ($vSUid)
						{
							$oVariant->price  = $vProduct->getPrice($vSUid, 1, $c_cat);
							$oVariant->seller = $vProduct->getSellerAttributes($vSUid);
							$oVariant->rating = $variantSeparate ? $this->helper->rating->getProductRating($product_id, $v_id) : $object->rating;

							if (($oVariant->seller->stock_capacity > 0 || !$hideNoSock) && (abs($oVariant->price->sales_price) >= 0.01 || !$hideZero))
							{
								$oVariant->shoprules = $this->helper->shopRule->toProduct($oVariant->price, true, true);
								$oVariant->code      = $vProduct->getCode($vSUid);
								$oVariant->images    = $vProduct->getImages(true, true);

								$object->variants[] = $oVariant;

								break;
							}
						}
					}
				}
			}

			$shoprules         = $this->helper->shopRule->toProduct($object, true, true);
			$object->shoprules = $shoprules;

			if (abs($price->list_price) >= 0.01)
			{
				$object->list_price = $object->list_price_final;
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

			$dispatcher->trigger('onProcessProduct', array('com_sellacious.product', $object));

			$cache[$cache_key] = $object;
		}

		return $cache[$cache_key];
	}

	/**
	 * Return a product item
	 *
	 * @param   int  $id  The id of the primary key
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function switchItem($id = null)
	{
		$multiVariant = $this->state->get($this->name . '.multi_variant');
		$multiSeller  = $this->state->get($this->name . '.multi_seller');

		$productId = $this->state->get($this->name . '.id', $id);
		$variantId = $this->state->get($this->name . '.variant_id');
		$sellerUid = $this->state->get($this->name . '.seller_uid');

		// Check if the selected variant is the variant of selected product, else ignore it
		$filter   = array('list.select' => 'a.id', 'product_id' => $productId);
		$variants = $multiVariant ? $this->helper->variant->loadColumn($filter) : array();

		if ($variantId && !in_array($variantId, $variants))
		{
			$variantId = 0;
		}

		// Check if the seller selected is valid and listed, else ignore it
		$sStocks = array();
		$records = $this->helper->product->getSellers($productId, true);

		foreach ($records as $seller)
		{
			$sStocks[$seller->seller_uid] = $seller->stock + $seller->over_stock;
		}

		if ($sellerUid && !array_key_exists($sellerUid, $sStocks))
		{
			$sellerUid = 0;
		}

		// Find a seller with stock for the selected variant
		$sUid = $this->findVariantSeller(array_keys($sStocks), $productId, $variantId, $sellerUid);

		if ($sUid)
		{
			$sellerUid = $sUid;
		}
		elseif ($variantId == 0 && $multiVariant)
		{
			// We could not find a seller with stock, we'd try another variant for current seller
			$productStock = ArrayHelper::getValue($sStocks, $sellerUid);
			$vId          = $this->findSellerVariant($variants, $productId, $variantId, $sellerUid, $productStock);

			if ($vId !== null)
			{
				$variantId = $vId;
			}
			// We couldn't find a variant with stock. We'd do over for all other variants x sellers now
			elseif ($multiSeller)
			{
				foreach ($variants as $vId)
				{
					if ($vId != $variantId)
					{
						$sUid = $this->findVariantSeller(array_keys($sStocks), $productId, $vId, $sellerUid);

						if ($sUid)
						{
							$sellerUid = $sUid;
							$variantId = $vId;
						}
					}
				}
			}
		}

		// Now we have the desired PVS
		$newCode = $this->helper->product->getCode($productId, $variantId, $sellerUid);

		$this->state->set($this->name . '.variant_id', $variantId);
		$this->state->set($this->name . '.seller_uid', $sellerUid);
		$this->state->set($this->name . '.code', $newCode);
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
			$seller = $this->helper->product->getSeller($product_id, $seller_uid);

			if (is_array($seller->query_form) && count($seller->query_form))
			{
				$cache = $this->helper->field->getListWithGroup($seller->query_form);
			}
		}

		return $cache;
	}

	/**
	 * Save the asked question for the product item
	 *
	 * @param    array   $data
	 *
	 * @return   int  Record id of the query
	 *
	 * @since    1.6.0
	 */
	public function saveQuestion($data)
	{
		$db   = JFactory::getDbo();

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
	 * @param   int[] $variants
	 * @param         $productId
	 * @param   int   $variantId
	 * @param   int   $sellerUid
	 * @param   int   $productStock
	 *
	 * @return  int
	 *
	 * @throws \Exception
	 * @since   1.7.0
	 */
	protected function findSellerVariant(array $variants, $productId, $variantId, $sellerUid, $productStock = 0)
	{
		// If main variant selected and has stock, nada!
		if ($variantId == 0 && $this->checkStockAndPrice($productId, 0, $sellerUid, $productStock))
		{
			return 0;
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
	 * @param   int[]  $sellers
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
	protected function findVariantSeller(array $sellers, $productId, $variantId, $sellerUid = null)
	{
		$multiSeller = $this->state->get($this->name . '.multi_seller');

		if (!$multiSeller)
		{
			// If Marketplace is disabled, get the stock and price from Default Seller
			$sellerUid = $this->state->get($this->name . '.default_seller');
			$sellers   = array($sellerUid);
		}

		if ($variantId)
		{
			$filter = array(
				'list.select' => 'seller_uid, stock + over_stock AS stock_capacity',
				'list.from'   => '#__sellacious_variant_sellers',
				'variant_id'  => $variantId,
				'seller_uid'  => $sellers,
			);
		}
		else
		{
			$filter = array(
				'list.select' => 'seller_uid, stock + over_stock AS stock_capacity',
				'list.from'   => '#__sellacious_product_sellers',
				'product_id'  => $productId,
				'seller_uid'  => $sellers,
			);
		}

		$records = $this->helper->variant->loadObjectList($filter);
		$stocks  = ArrayHelper::getColumn($records, 'stock_capacity', 'seller_uid');

		// If seller is selected and has stock, voila!
		if ($sellerUid)
		{
			$stock = ArrayHelper::getValue($stocks, $sellerUid);

			if ($this->checkStockAndPrice($productId, $variantId, $sellerUid, $stock))
			{
				return $sellerUid;
			}
		}

		// Find a seller who has stock, switch!
		if ($multiSeller)
		{
			foreach ($stocks as $sUid => $stock)
			{
				if ($this->checkStockAndPrice($productId, $variantId, $sUid, $stock))
				{
					return $sUid;
				}
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
		$hideZero   = $this->helper->config->get('hide_zero_priced');
		$hideNoSock = $this->helper->config->get('hide_out_of_stock');

		if (!$hideNoSock || $stock > 0)
		{
			if (!$hideZero)
			{
				return true;
			}

			$me      = JFactory::getUser();
			$c_cat   = $this->helper->client->getCategory($me->id, true);
			$product = new ProductItem($productId, $variantId);
			$price   = $product->getPrice($sellerUid, 1, $c_cat);

			if ($price->sales_price >= 0.01)
			{
				return true;
			}
		}

		return false;
	}
}
