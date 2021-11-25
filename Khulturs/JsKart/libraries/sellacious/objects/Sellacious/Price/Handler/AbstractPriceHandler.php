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
use JHtml;
use JLayoutHelper;
use Joomla\Registry\Registry;
use stdClass;

defined('_JEXEC') or die;

/**
 * Base class for product pricing handlers
 *
 * @since   2.0.0
 */
abstract class AbstractPriceHandler
{
	/**
	 * The handler name
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The handler title
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $title;

	/**
	 * The handler description
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $description;

	/**
	 * AbstractPriceHandler constructor.
	 *
	 * @param   string  $name         The handler name
	 * @param   string  $title        The handler title
	 * @param   string  $description  The handler description
	 *
	 * @since   2.0.0
	 */
	public function __construct($name, $title, $description)
	{
		$this->name        = $name;
		$this->title       = $title;
		$this->description = $description;
	}

	/**
	 * The handler name
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * The handler title
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * The handler description
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Render the relevant layout for this pricing type
	 * The layout will be prefixed with 'sellacious.pricing_type.[$name].[$client].' automatically
	 *
	 * @param   string  $layout  The context and element name to be rendered,  e.g. - 'add-to-cart.product'
	 * @param   mixed   $data    The product record or any relevant data required by the layout
	 * @param   string  $client  The client name, e.g. - site, sellacious, administrator
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function renderLayout($layout, $data, $client = null)
	{
		$this->loadLanguage();

		return JLayoutHelper::render($this->getLayout($layout, $client), $data);
	}

	/**
	 * Render the relevant vue layout for this pricing type
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function renderVueLayout()
	{
		$this->loadLanguage();

		$this->renderVueTemplate('price', 'Price.vue');
		$this->renderVueTemplate('addtocart', 'AddToCart.vue');
		$this->renderVueTemplate('buynow', 'BuyNow.vue');
		$this->renderVueTemplate('stock', 'Stock.vue');

		return true;
	}

	/**
	 * Render the relevant vue template for this pricing type. If not found, render the hidden pricing type layout.
	 * The layout will be prefixed with 'sellacious.pricing_type.[$name].[$client].' automatically
	 * TODO: Change $path to dot notation after implementing JLayout in 'ctech.vueTemplate' function
	 *
	 * @param   string  $id        Lowercase name of the type of layout to be rendered, e.g. - 'price' || 'addtocart' || 'buynow'
	 * @param   string  $fileName  Name of the vue file to be rendered
	 *
	 * @since   2.0.0
	 */
	protected function renderVueTemplate($id, $fileName)
	{
		$name    = $this->getName();
		$vuePath = JPATH_ROOT . "/layouts/sellacious/pricing-type/{$name}/site/blocks/vue/{$fileName}";

		if (!file_exists($vuePath))
		{
			$vuePath = JPATH_ROOT . '/layouts/sellacious/pricing-type/hidden/site/blocks/vue/' . $fileName;
		}

		JHtml::_('ctech.vueTemplate', "vue-product-{$id}-{$name}", $vuePath);
	}

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
	abstract public function getProductPrices($productId, $variantId, $sellerUid, $quantity = null, $client_catid = null);

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
	abstract public function setPricesForCache($productId, array &$items);

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
	abstract public function processCacheProduct($item);

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
	abstract public function isValidPrice($productId, $variantId, $sellerUid);

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

		return sprintf('sellacious.pricing-type.%s.%s.%s', $this->getName(), $client, $layout);
	}

	/**
	 * Method to save the product prices
	 *
	 * @param   int       $productId  The product id
	 * @param   int       $sellerUid  The seller user id
	 * @param   Registry  $registry   The submitted product form data
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function saveProduct($productId, $sellerUid, Registry $registry)
	{
		// Move here from model
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
	}

	/**
	 * Method to load languages
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function loadLanguage()
	{
	}

	/**
	 * Method to match the given pricing type with pattern
	 *
	 * @param   string  $pricingType
	 *
	 * @return  bool|int
	 *
	 * @since   2.0.0
	 */
	public function matchPricingType($pricingType)
	{
		return preg_match('/' . preg_quote($this->name, '/') . '/', $pricingType);
	}
}
