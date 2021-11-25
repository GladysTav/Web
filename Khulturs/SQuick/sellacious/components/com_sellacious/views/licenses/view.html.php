<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of licenses.
 *
 * @since  3.0
 */
class SellaciousViewLicenses extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'license';

	/** @var  string */
	protected $view_item = 'license';

	/** @var  string */
	protected $view_list = 'licenses';

	/** @var array */
	protected $lists = array();
}