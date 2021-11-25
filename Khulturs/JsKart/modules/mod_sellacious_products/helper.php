<?php
/**
 * @version     2.0.0
 * @package     Sellacious Products Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Config\ConfigHelper;
use Sellacious\Price\PriceHelper;

/**
 * @package   Sellacious Products Module
 *
 * @since     2.0.0
 */
class ModSellaciousProductsHelper
{
	/**
	 * @var   JApplicationCms
	 *
	 * @since   2.0.0
	 */
	protected $app;

	/**
	 * @var   SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * @var   ProductsCacheReader
	 *
	 * @since   2.0.0
	 */
	protected $loader;

	/**
	 * @var   Registry
	 *
	 * @since   2.0.0
	 */
	protected $params;

	/**
	 * @var   string
	 *
	 * @since   2.0.0
	 */
	protected $module_type;

	/**
	 * Method to get products for a Module
	 *
	 * @param   Registry  $params  The module parameters
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getList(Registry $params)
	{
		$this->params = $params;
		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();
		$this->loader = new ProductsCacheReader;

		$this->loader->filterValue('is_visible', 0, '>');
		$this->loader->getQuery()->order('is_visible DESC');

		$this->module_type = $params->get('module_type', 'default');

		$this->getProductsDefault();

		switch ($this->module_type)
		{
			case 'latest':
				$this->getProductsLatest();
				break;
			case 'related':
				$this->getProductsRelated();
				break;
			case 'bestselling':
				$this->getProductsBestSelling();
				break;
			case 'recently_viewed':
				$this->getProductsRecentlyViewed();
				break;
			case 'seller':
				$this->getProductsSellerWise();
				break;
			case 'special_cats':
				// Not implemented maybe?
				break;
		}

		try
		{
			$limit = $params->get('total_products', '10');
			$items = $this->loader->getItems(0, $limit);

			return $this->processList($items);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return array();
		}
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function processList(&$items)
	{
		foreach ($items as &$item)
		{
			$item->category         = json_decode($item->category, true);
			$item->category_ext     = json_decode($item->category_ext, true);
			$item->spl_category     = json_decode($item->spl_category, true);
			$item->specifications   = json_decode($item->specifications, true);
			$item->product_rating   = json_decode($item->product_rating);
			$item->product_features = json_decode($item->product_features);

			$handler = PriceHelper::getHandler($item->pricing_type);

			$handler->processCacheProduct($item);

			$item->rendered_attributes = $this->helper->product->getRenderedAttributes($item->code, $item->product_id, $item->variant_id, $item->seller_uid);
		}

		return $items;
	}

	protected function getProductsDefault()
	{
		try
		{
			$config = ConfigHelper::getInstance('com_sellacious');

			if ($config->get('hide_zero_priced'))
			{
				// $this->loader->getQuery()->where('(sales_price > 0 OR price_display > 0)');
			}

			if ($config->get('hide_out_of_stock'))
			{
				$this->loader->filterValue('stock_capacity', 0,  '>');
			}
		}
		catch (Exception $e)
		{
		}

		$language = JFactory::getLanguage()->getTag();

		if ($language)
		{
			$this->loader->filterValue('language', array($language, '*', ''));
		}

		$this->loader->filterValue('product_active', 1);
		$this->loader->filterValue('listing_active', 1);
		$this->loader->filterValue('seller_active', 1);
		$this->loader->filterValue('is_selling', 1);

		if (($this->module_type != 'related'))
		{
			$filter_products = $this->params->get('products');
			$filter_products = is_array($filter_products) ? $filter_products : array_map('intval', explode(',', $filter_products));
			$filter_products = array_unique(array_filter($filter_products));

			if ($filter_products)
			{
				$this->loader->filterValue('product_id', $filter_products, 'IN');
			}
		}

		$filter_categories = $this->params->get('categories');

		if ($filter_categories)
		{
			$this->loader->filterIntersectJsonKey('category_ext', null, $filter_categories);
		}

		$spl_category = $this->params->get('splcategory', 0);

		if ($spl_category)
		{
			$this->loader->filterInJsonKey('spl_category', null, $spl_category);
		}

		$this->loader->getQuery()->where('(variant_id = 0 OR variant_active = 1)');

		switch ($this->params->get('ordering', 'order_units'))
		{
			case 'rating_max':
				$ord = 'product_rating DESC';
				break;
			case 'price_min':
				$ord = 'sales_price * forex_rate ASC';
				break;
			case 'price_max':
				$ord = 'sales_price * forex_rate DESC';
				break;
			default:
				$ord = 'order_units DESC';
				break;
		}

		$this->loader->getQuery()->order($ord);

	}

	protected function getProductsRelated()
	{
		$productId       = 0;
		$filter_products = array();
		$related_for     = $this->params->get('related_for', 'page');

		if ($related_for === 'module' || $related_for === '')
		{
			$filter_products = $this->params->get('products');
			$filter_products = is_array($filter_products) ? $filter_products : array_map('intval', explode(',', $filter_products));
			$filter_products = array_unique(array_filter($filter_products));
		}

		if ($related_for === 'page' || $related_for === '')
		{
			$option = $this->app->input->getCmd('option');
			$view   = $this->app->input->getCmd('view');
			$pCode  = $this->app->input->getString('p');

			if ($option === 'com_sellacious' && $view === 'product' && $pCode)
			{
				$this->helper->product->parseCode($pCode, $productId, $variantId, $sellerUid);
			}
		}

		if ($productId)
		{
			$filter_products[] = $productId;
		}

		if ($filter_products)
		{
			$this->loader->filterIntersectJsonArray('related_products', null, $filter_products);
		}
		else
		{
			$this->loader->fallacy();
		}
	}

	protected function getProductsBestSelling()
	{
		$this->loader->filterValue('order_count', 0, '>');

		$this->loader->getQuery()->clear('order')->order('order_count DESC');
	}

	protected function getProductsRecentlyViewed()
	{
		$option = $this->app->input->getString('option');
		$view   = $this->app->input->getString('view');
		$pCode  = array();

		if ($option === 'com_sellacious' && $view === 'product')
		{
			$pCode = (array) $this->app->input->getString('p');
		}

		$session = JFactory::getSession();
		$codes   = $session->get('sellacious.lastviewed', array());
		$codes   = array_diff($codes, $pCode);

		if ($codes)
		{
			$this->loader->filterValue('code', $codes, 'IN');
		}
		else
		{
			$this->loader->fallacy();
		}
	}

	protected function getProductsSellerWise()
	{
		$sellerUid = null;

		// By seller uid or seller category id
		$showBy  = $this->params->get('products_by', 'sid');
		$exclude = (int) $this->params->get('exclude_on_detail');

		if ($exclude)
		{
			$option = $this->app->input->getCmd('option');
			$view   = $this->app->input->getCmd('view');
			$pCode  = $this->app->input->getString('p');
			$sId    = $this->app->input->getInt('id');

			if ($option === 'com_sellacious' && $view === 'product' && $pCode)
			{
				$this->helper->product->parseCode($pCode, $productId, $variantId, $sellerUid);
			}
			elseif ($option === 'com_sellacious' && $view === 'store' && $sId)
			{
				$sellerUid = $sId;
			}

			if ($showBy == 'sid')
			{
				$sid = $sellerUid;
			}
			else
			{
				$sid = $this->helper->seller->getCategory($sellerUid, true);
			}

			$sellers = $sid ? array($sid) : null;
		}
		else
		{
			$sellers = $this->params->get('sellers');
			$sellers = array_unique(array_filter(array_map('intval', explode(',', $sellers))));
		}

		if ($sellers)
		{
			$this->loader->filterValue($showBy == 'sid' ? 'seller_uid' : 'seller_catid', $sellers, 'IN');
		}
	}

	protected function getProductsLatest()
	{
		$this->loader->getQuery()->clear('order')->order('listing_start DESC')->order('is_visible DESC');
	}
}
