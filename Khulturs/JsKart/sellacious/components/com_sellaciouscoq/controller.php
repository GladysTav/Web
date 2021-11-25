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
 * Checkout Questions Controller
 *
 * @since  2.0.0
 */
class SellaciouscoqController extends SellaciousControllerBase
{
	/**
	 * Display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   2.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'coq');

		$this->input->set('view', $view);

		return parent::display($cachable, $urlparams);
	}
}
