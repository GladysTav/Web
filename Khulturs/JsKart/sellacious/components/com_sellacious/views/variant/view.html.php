<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * View class for a product variant.
 *
 * @since   2.0.0
 */
class SellaciousViewVariant extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $action_prefix = 'variant';

	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_item = 'variant';

	/**
	 * @var  string
	 *
	 * @since   2.0.0
	 */
	protected $view_list = 'variants';

	/**
	 * Add the page title and toolbar
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function addToolbar()
	{
		$this->app->input->set('tmpl', 'component');
	}
}
