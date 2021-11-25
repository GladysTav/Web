<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of templates.
 *
 * @since  1.7.0
 */
class SellaciousTemplatesViewTemplates extends SellaciousViewList
{
	/**
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $action_prefix = 'template';

	/**
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $view_item = 'template';

	/**
	 * @var    string
	 *
	 * @since  1.7.0
	 */
	protected $view_list = 'templates';

	/**
	 * @var    array
	 *
	 * @since  1.7.0
	 */
	protected $lists = array();

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.7.0
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();
	}
}
