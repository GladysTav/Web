<?php
/**
 * @version     1.7.4
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/**
 * Reporting Controller
 *
 * @since  1.6.0
 */
class SellaciousReportingController extends SellaciousControllerBase
{
	/**
	 * Display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.6.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'reports');
		$this->input->set('view', $view);

		if (!$this->helper->core->isRegistered())
		{
			$this->input->set('tmpl', 'component');
			$this->input->set('view', 'activation');
		}
		elseif (!$this->helper->core->isConfigured())
		{
			$this->input->set('tmpl', 'component');
			$this->input->set('view', 'setup');
		}
		elseif (!$this->canView())
		{
			$tmpl   = $this->input->get('tmpl', null);
			$suffix = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
			$return = JRoute::_('index.php?option=com_sellacious' . $suffix, false);

			if ($tmpl != 'raw')
			{
				$this->setRedirect($return);

				JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			}

			return $this;
		}

		return parent::display($cachable, $urlparams);
	}

	/**
	 * Checks whether a user can see this view.
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	protected function canView()
	{
		$view = $this->input->get('view', 'dashboard');

		/**
		 * Todo: Below we assume all (backend) singular views to be edit layout only.
		 * Todo: This may not be true. 'create' etc permissions need to be checked too.
		 */
		switch ($view)
		{
			case 'reports':
				$allow = $this->canList('report');
				break;
			case 'report':
			case 'sreports':
				$allow = $this->canEdit('report');
				break;
			default:
				$allow = false;
		}

		return $allow;
	}

	/**
	 * Check whether the user can view the plural/list view or not
	 *
	 * @param   string  $asset
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	protected function canList($asset)
	{
		$b = $this->helper->access->check($asset . '.list') ||
			$this->helper->access->check($asset . '.list.own');

		return $b;
	}

	/**
	 * Check whether the user can view the singular/edit view or not
	 *
	 * @param   string  $asset
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	protected function canEdit($asset)
	{
		$rules = array('.create', '.edit', '.edit.own');

		return $this->helper->access->checkAny($rules, $asset);
	}
}
