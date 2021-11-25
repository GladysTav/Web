<?php
/**
 * @version     2.0.0
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\CMS\Response\JsonResponse;

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/module.php';

/**
 * @package  Sellacious\ProductsFilter
 *
 * @since   2.0.0
 */
class ModSellaciousFiltersHelper
{
	/**
	 * Get auto complete list of locations by ajax
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function getAutoCompleteSearchAjax()
	{
		$db     = JFactory::getDbo();
		$app    = JFactory::getApplication();
		$helper = SellaciousHelper::getInstance();

		$term     = $app->input->getString('term');
		$searchIn = $app->input->get('search_in', array(), 'array');
		$start    = $app->input->getInt('list_start', 0);
		$limit    = $app->input->getInt('list_limit', 5);

		$filters = array(
			'list.select' => 'CONCAT(a.title, IFNULL(CONCAT(", ", a.area_title), ""), IFNULL(CONCAT(", ", a.state_title), ""), IFNULL(CONCAT(", ", a.country_title), "")) AS value, a.id',
			'list.order'  => 'a.title',
			'list.start'  => $start,
			'list.limit'  => $limit,
			'type'        => $searchIn ?: 'country',
			'state'       => '1',
			'list.where'  => array('a.parent_id >= 1'),
		);

		if (strlen($term))
		{
			$filters['list.where'][] = 'a.title LIKE ' . $db->q($db->escape($term, true) . '%', false);
		}

		$items = $helper->location->loadObjectList($filters);

		echo json_encode($items);

		jexit();
	}

	/**
	 * Get product categories filter levels by ajax
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function getFilterAjax()
	{
		try
		{
			$app  = JFactory::getApplication();
			$name = $app->input->getString('filter');
			$args = $app->input->get('args', array(), 'array');

			$module = ModSellaciousFilters::getInstance();
			$filter = $module->getFilter($name);
			$data   = $filter->handleAjaxCall($args);

			echo new JsonResponse($data);
		}
		catch (Exception $e)
		{
			echo new JsonResponse($e);
		}

		jexit();
	}

	/**
	 * Ajax Method to clear filters
	 *
	 * @since   1.6.0
	 */
	public static function clearFiltersAjax()
	{
		try
		{
			$app = JFactory::getApplication();
			$app->setUserState('com_sellacious.products.filter', null);

			echo new JResponseJson;
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

		jexit();
	}
}
