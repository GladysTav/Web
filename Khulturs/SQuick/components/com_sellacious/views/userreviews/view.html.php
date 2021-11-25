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
defined('_JEXEC') or die;

/**
 * View class for a list of user posted reviews.
 *
 * @since  2.0.0
 */
class SellaciousViewUserReviews extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 *
	 * @since  2.0.0
	 */
	protected $items;

	/**
	 * @var  JPagination
	 *
	 * @since  2.0.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since  2.0.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function display($tpl = null)
	{
		// Preserve state info
		$this->state = $this->get('State');

		$authorId = $this->state->get('filter.author_id', 0);

		if (!$authorId)
		{
			$this->helper->core->checkGuest();
		}

		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::ERROR, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}

	/**
	 * Method to get Author info
	 *
	 * @return \Joomla\CMS\User\User
	 *
	 * @since   2.0.0
	 */
	public function getAuthorInfo()
	{
		$authorId = $this->state->get('filter.author_id', 0);
		$author   = JFactory::getUser($authorId);

		return $author;
	}
}
