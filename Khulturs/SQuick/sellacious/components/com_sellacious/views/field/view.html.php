<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit
 */
class SellaciousViewField extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'field';

	/** @var  string */
	protected $view_item = 'field';

	/** @var  string */
	protected $view_list = 'fields';
}
