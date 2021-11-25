<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Product;

defined('_JEXEC') or die('Restricted access');

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * The google Structured markup plugin for product listing and product detail pages
 *
 * @since   1.6.0
 */
class plgSystemSellaciousGoogleMarkup extends SellaciousPlugin
{
	/**
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $hasConfig = true;

	/**
	 * @var    object
	 *
	 * @since  1.6.0
	 */
	protected $db;

	/**
	 * Log entries collected during execution.
	 *
	 * @var    array
	 *
	 * @since  1.6.0
	 */
	protected $log = array();

	/**
	 * Constructor
	 *
	 * @param   object &$subject  The object to observe
	 * @param   array  $config    An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		$this->helper     = SellaciousHelper::getInstance();
		$this->pluginName = 'plg_' . $this->_type . '_' . $this->_name;
		$this->pluginPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;

		if ($this->hasConfig)
		{
			$this->params = $this->helper->config->getParams($this->pluginName);
		}

		$options = array('text_file' => $this->pluginName . '-log.php');

		JLog::addLogger($options, JLog::ALL, array($this->pluginName));
	}

	/**
	 * Add Google JSON-LD structured data to page
	 *
	 * @param    string           $context  The context
	 * @param    \SellaciousView  $view     The view data
	 *
	 * @return   void
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function onBeforeDisplayView($context, $view)
	{
		if ($context == 'com_sellacious.product' && $this->app->isClient('site'))
		{
			$product       = $view->get('item');
			$googleProduct = $this->getGoogleProductData($product);

			if (!empty($googleProduct))
			{
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(json_encode($googleProduct, JSON_PRETTY_PRINT), 'application/ld+json');
			}
		}
		elseif ($context == 'com_sellacious.products' && $this->app->isClient('site'))
		{
			$products       = $view->get('items');
			$googleProducts = array();

			foreach ($products as $product)
			{
				$googleProducts[] = $this->getGoogleProductData($product);
			}

			if (!empty($googleProducts))
			{
				$document = JFactory::getDocument();
				$document->addScriptDeclaration(json_encode($googleProducts, JSON_PRETTY_PRINT), 'application/ld+json');
			}
		}
	}

	/**
	 * Create Google Structured Data for Product
	 *
	 * @param   \stdClass  $item  Product details
	 *
	 * @return   array
	 *
	 * @throws   \Exception
	 *
	 * @since    1.6.0
	 */
	public function getGoogleProductData($item)
	{
		$googleProduct = array();

		$product       = new Product($item->id, $item->variant_id, $item->seller_uid);
		$productImages = $product->getImages(true, false);
		$images        = array();

		$item->product_title        = isset($item->title) ? $item->title : $item->product_title;
		$item->product_description  = isset($item->description) ? $item->description : $item->product_description;
		$item->product_sku          = isset($item->local_sku) ? $item->local_sku : $item->product_sku;
		$item->product_rating       = isset($item->rating) ? $item->rating : $item->product_rating;
		/*Cannot process manufacturer name*/
		//$item->manufacturer_company = isset($item->manufacturer) ? $item->manufacturer : $item->manufacturer_company;

		$googleProduct['@context'] = 'http://schema.org/';
		$googleProduct['@type']    = 'Product';
		$googleProduct['name']     = $item->product_title;

		// Attach Product images to the markup
		foreach ($productImages as $image)
		{
			$images[] = JUri::root() . $image;
		}

		if (!empty($images))
		{
			$googleProduct['image'] = $images;
		}

		$googleProduct['description'] = strip_tags($item->product_description);
		$googleProduct['sku']         = $item->product_sku;

		if ($item->variant_id > 0)
		{
			$googleProduct['sku'] = $item->variant_sku;
		}

		// Attach Brand/Manufacturer
		$googleProduct['brand'] = array('@type' => 'Brand', 'name' => @$item->manufacturer_company);

		// Attach Rating/Review
		$rating = $item->product_rating;

		if ($rating->count)
		{
			$googleProduct['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'worstRating' => 0,
				'ratingValue' => $rating->rating,
				'reviewCount' => (int) $rating->count,
			);
		}

		// Attach price/offer details
		$googlePrice                  = array();
		$googlePrice['@type']         = 'Offer';
		$googlePrice['priceCurrency'] = $item->seller_currency;
		$googlePrice['price']         = isset($item->price) ? $item->price->sales_price : (isset($item->sales_price) ? $item->sales_price : 0);

		if ($item->stock_capacity > 0)
		{
			$googlePrice['availability'] = 'http://schema.org/InStock';
		}
		else
		{
			$googlePrice['availability'] = 'http://schema.org/OutOfStock';
		}

		$googlePrice['seller']   = array('@type' => 'Organization', 'name' => $item->seller_company ? : $item->seller_name);
		$googlePrice['url']      = JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $item->code, false);
		$googleProduct['offers'] = $googlePrice;

		return $googleProduct;
	}
}

endif;
