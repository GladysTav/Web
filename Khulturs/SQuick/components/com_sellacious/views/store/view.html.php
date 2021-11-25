<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JLoader::register('SellaciousViewProducts', dirname(__DIR__) . '/products/view.html.php');


/**
 * View class for a list of products by a seller
 *
 * @since   1.2.0
 */
class SellaciousViewStore extends SellaciousViewProducts
{
	/**
	 * @var  JObject
	 *
	 * @since   1.2.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.2.0
	 */
	public function display($tpl = null)
	{
		$storeId = $this->app->input->getInt('id');

		if (!$storeId)
		{
			JLog::add(JText::_('COM_SELLACIOUS_STORE_SELECTED_INVALID_SHOWING_ALL_STORES_MESSAGE'), JLog::WARNING, 'jerror');

			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=products', false));
		}

		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}
}
