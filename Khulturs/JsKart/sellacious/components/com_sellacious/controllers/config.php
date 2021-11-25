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
use Sellacious\Access\AccessHelper;

defined('_JEXEC') or die;

/**
 * Config controller class
 *
 * @since   1.2.0
 */
class SellaciousControllerConfig extends SellaciousControllerForm
{
	/**
	 * @var		string	The name of the list view related to this
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'config';

	/**
	 * @var		string	The prefix to use with controller messages
	 *
	 * @since   1.2.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CONFIG';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	protected function allowSave($data, $key = 'id')
	{
		return AccessHelper::allow('config.edit');
	}
}
