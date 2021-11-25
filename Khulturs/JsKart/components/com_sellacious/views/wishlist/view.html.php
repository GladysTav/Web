<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of categories
 *
 * @since  1.0.0
 */
class SellaciousViewWishlist extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * @var  JPagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}
}
