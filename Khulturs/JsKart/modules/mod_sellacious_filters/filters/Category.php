<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\ProductsFilter;

// no direct access
use Exception;
use JFactory;
use JModuleHelper;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;
use stdClass;

defined('_JEXEC') or die;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
class Category extends AbstractFilter
{
	/**
	 * Flag to indicate whether a list is limited due to configured limit and has more items to show
	 *
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	protected $is_limited;

	/**
	 * Method to compute and assign the values to be cached for the filter to work
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function buildCacheData()
	{

	}

	/**
	 * Method to populate the state with the filter values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function populateState()
	{
		// Handled by model
	}

	/**
	 * Method to apply the filter to search query using the state with values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addFilter()
	{
		// Handled by model
	}

	/**
	 * Get required data for the filter form, if any
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getFilterFormData()
	{
		$storeId = $this->module->getState()->get('store.id');
		$catId   = $this->module->getState()->get('filter.category_id', 1);
		$limit   = $this->module->getCfg('categories_limit');
		$limit2  = $this->module->getCfg('categories_limit2');
		$cats    = $this->getCategories();
		$items   = $this->buildLevels($cats, $limit, $limit2);

		return array('items' => $items, 'showShowAll' => $this->is_limited, 'storeId' => $storeId, 'catId' => $catId);
	}

	/**
	 * Method to render the frontend filter form which can be submitted to apply a products filter
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function renderForm()
	{
		if ($this->app->input->getCmd('view') === 'stores')
		{
			return;
		}

		parent::renderForm();
	}

	/**
	 * Render layout recursive for child nodes
	 *
	 * @param   stdClass[]  $items    Hierarchical list of categories
	 * @param   int         $storeId  Store id if it a store page
	 * @param   int         $catId    Category id selected
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function renderLevel($items, $storeId, $catId)
	{
		func_get_args();

		/** @noinspection  PhpIncludeInspection */
		include JModuleHelper::getLayoutPath('mod_sellacious_filters', 'filter_category_level');
	}

	/**
	 * Get All Categories List
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function getCategories()
	{
		$catId = (int) $this->module->getState()->get('filter.category_id', 1);

		try
		{
			$config  = ConfigHelper::getInstance('com_sellacious');
			$allowed = $config->get('allowed_product_type', 'both');
			$package = $config->get('allowed_product_package', 1);
		}
		catch (Exception $e)
		{
			$allowed = 'both';
			$package = 1;
		}

		$allowed = $allowed == 'both' ? array('product/physical', 'product/electronic') : array('product/' . $allowed);

		if ($package)
		{
			$allowed[] = 'product/package';
		}

		$filter  = array(
			'list.select' => 'a.id, a.title, a.parent_id, a.type, a.lft, a.rgt',
			'type'        => $allowed,
			'state'       => 1,
			'list.where'  => array(),
			'list.order'  => array('a.lft ASC'),
		);

		if ($catId <= 1)
		{
			$parents = array(1);
			$levels  = $this->module->getCfg('categories_levels', 3);

			$filter['list.where'][] = 'a.level <= ' . (int) $levels;
		}
		else
		{
			$category = $this->helper->category->getItem($catId);

			$filter['list.where'][] = 'a.level <= ' . ($category->level + 1);

			if ($category->parent_id > 1)
			{
				$parents = $this->helper->category->getParents($catId, true);

				$filter['list.where'][] = sprintf('(a.id IN (%s) OR a.parent_id IN (1, %d, %d))', implode(',', $parents), $category->parent_id, $category->id);
			}
			else
			{
				$parents = array(1, $catId);
			}
		}

		$categories    = $this->helper->category->loadObjectList($filter, 'id');
		$root          = (object) array('id' => 1, 'parent_id' => 0, 'children' => array());
		$categories[1] = $root;

		$language = JFactory::getLanguage()->getTag();

		foreach ($categories as $c)
		{
			$this->helper->translation->translateRecord($c, 'sellacious_categories', $language);
		}

		$items = array();

		foreach ($parents as $i)
		{
			if (isset($categories[$i]))
			{
				$items[$i] = $categories[$i];

				unset($categories[$i]);
			}
		}

		foreach ($categories as $cId => $category)
		{
			$items[$cId] = $category;
		}

		return $items;
	}

	/**
	 * Generate levels (multi dimension array) from a linear array
	 *
	 * @param   stdClass[]  $items   Flat list of items to build levels with
	 * @param   int         $limit   The no. of records to show in top level
	 * @param   int         $limit2  The no. of records to show in sub category level
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function buildLevels($items, $limit = 10, $limit2 = 10)
	{
		$result = array();

		if (isset($items[1]))
		{
			if (!isset($items[1]->children))
			{
				$items[1]->children = array();
			}

			foreach ($items as $id => &$item)
			{
				if (isset($items[$item->parent_id]))
				{
					$node = &$items[$item->parent_id];
					$lim  = ($item->parent_id > 1) ? $limit2 : $limit;

					if ($lim > 0 && isset($node->children) && count($node->children) >= $lim)
					{
						$this->is_limited = true;
					}
					else
					{
						$node->children[] = &$item;
					}
				}
			}

			$result = &$items[1]->children;
		}

		return $result;
	}

	/**
	 * Get a filter specific response from the filter instance via ajax
	 *
	 * @param   array  $args  The ajax arguments
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function handleAjaxCall($args)
	{
		if (isset($args['scope']) && $args['scope'] === 'html')
		{
			$catId   = ArrayHelper::getValue($args, 'category_id', 1, 'int');
			$storeId = ArrayHelper::getValue($args, 'store_id', 0, 'int');

			$this->module->getState()->set('filter.category_id', $catId);
			$this->module->getState()->set('store.id', $storeId);

			$cats  = $this->getCategories();
			$items = $this->buildLevels($cats, 0, 0);

			ob_start();

			$this->renderLevel($items, $storeId, $catId);

			return ob_get_clean();
		}

		return null;
	}
}
