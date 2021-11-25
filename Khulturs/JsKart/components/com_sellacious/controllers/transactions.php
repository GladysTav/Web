<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * list controller class
 *
 * @since  2.0.0
 */
class SellaciousControllerTransactions extends SellaciousControllerBase
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_TRANSACTIONS';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   2.0.0
	 */
	public function getModel($name = 'Transaction', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
