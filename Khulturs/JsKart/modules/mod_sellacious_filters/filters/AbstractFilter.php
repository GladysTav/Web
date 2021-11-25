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
use JApplicationCms;
use JFactory;
use JModuleHelper;
use Joomla\String\Normalise;
use ModSellaciousFilters;
use SellaciousHelper;

defined('_JEXEC') or die;

/**
 * Filter Class
 *
 * @package  Sellacious\ProductsFilter
 *
 * @since    2.0.0
 */
abstract class AbstractFilter
{
	/**
	 * Name for this filter class
	 *
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $name;

	/**
	 * The module class instance
	 *
	 * @var  ModSellaciousFilters
	 *
	 * @since   2.0.0
	 */
	protected $module;

	/**
	 * Global application instance
	 *
	 * @var  JApplicationCms
	 *
	 * @since   2.0.0
	 */
	protected $app;

	/**
	 * Sellacious helper instance
	 *
	 * @var  SellaciousHelper
	 *
	 * @since   2.0.0
	 */
	protected $helper;

	/**
	 * Whether to submit filter form automatically if a filter is changed
	 *
	 * @var   bool
	 *
	 * @since   2.0.0
	 */
	protected $autoSubmit;

	/**
	 * Constructor
	 *
	 * @param   ModSellaciousFilters
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($module)
	{
		$this->module = $module;
		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();

		$this->autoSubmit = $this->module->getCfg('apply_filters_by') === 'individual';
	}

	/**
	 * Get the name for this form
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		if (!$this->name)
		{
			preg_match('/[^\\\]+$/', static::class, $matches);

			$this->name = strtolower(Normalise::toUnderscoreSeparated(Normalise::fromCamelCase($matches[0])));
		}

		return $this->name;
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
		$displayData = $this->getFilterFormData();

		/** @noinspection  PhpIncludeInspection */
		include JModuleHelper::getLayoutPath('mod_sellacious_filters', 'filter_' . $this->getName());
	}

	/**
	 * Method to compute and assign the values to be cached for the filter to work
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function buildCacheData();

	/**
	 * Method to populate the state with the filter values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function populateState();

	/**
	 * Method to apply the filter to search query using the state with values submitted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	abstract public function addFilter();

	/**
	 * Get required data for the filter form, if any
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	abstract protected function getFilterFormData();

	/**
	 * Get a filter specific response from the filter instance via ajax
	 *
	 * @param   array  $args  The ajax arguments
	 *
	 * @return  mixed
	 *
	 * @since   2.0.0
	 */
	public function handleAjaxCall($args)
	{
		return null;
	}
}
