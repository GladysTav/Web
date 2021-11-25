<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of categories.
 *
 * @since   1.7.0
 */
class SellaciousViewSupport extends SellaciousView
{
	/**
	 * Display the view
	 *
	 * @param   string $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.7.0
	 */
	public function display($tpl = null)
	{
		$enabled = $this->helper->config->get('support_mode.enable');

		$this->setLayout($enabled ? 'support' : 'default');

		return parent::display($tpl);
	}
}
