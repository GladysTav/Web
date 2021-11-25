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
use Sellacious\Access\AccessHelper;

defined('_JEXEC') or die;

/**
 * View class for a Comparison.
 */
class SellaciousViewCompare extends SellaciousView
{
	/** @var  stdClass[] */
	protected $items;

	/** @var  JObject */
	protected $state;

	/** @var  stdClass[] */
	protected $groups;

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
		if (!$this->helper->config->get('product_compare') && !AccessHelper::allow('app.manage'))
		{
			JLog::add(JText::_('COM_SELLACIOUS_PAGE_NOT_FOUND'), JLog::WARNING);

			$default  = $this->app->getMenu()->getDefault();
			$homePage = JRoute::_('index.php?Itemid=' . $default->id, false);
			$redirect = $this->helper->config->get('redirect') ?: $homePage;

			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_COMPARE_FEATURE_DISABLED'), 'info');
			$this->app->redirect($redirect);

			// This is actually not needed
			return false;
		}

		$this->state  = $this->get('State');
		$this->items  = $this->get('Items');
		$this->groups = $this->get('Attributes');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}
}
