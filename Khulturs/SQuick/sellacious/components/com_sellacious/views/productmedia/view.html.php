<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
use Sellacious\Toolbar\ToolbarHelper;

// no direct access
defined('_JEXEC') or die;

/**
 * View to edit
 *
 * @since   2.0.0
 */
class SellaciousViewProductMedia extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $action_prefix = 'productmedia';

	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_item = 'productmedia';

	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_list = 'productmedia';

	/**
	 * @var  \stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected $items;

	/**
	 * Add the page title and toolbar
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function addToolbar()
	{
		if ($this->_layout === 'items')
		{
			$this->items = $this->get('Items');
			$this->app->input->set('tmpl', 'raw');
		}
		else
		{
			$this->app->input->set('tmpl', 'component');
		}
	}
}
