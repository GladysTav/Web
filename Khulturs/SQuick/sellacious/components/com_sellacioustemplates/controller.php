<?php
/**
 * @version     2.0.0
 * @package     com_sellacioustemplates
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/**
 * Templates Controller
 *
 * @since  1.7.0
 */
class SellaciousTemplatesController extends SellaciousControllerBase
{
	/**
	 * Display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.7.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'templates');
		$this->input->set('view', $view);

		return parent::display($cachable, $urlparams);
	}
}
