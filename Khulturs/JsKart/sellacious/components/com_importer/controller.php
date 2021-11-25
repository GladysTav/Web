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

use Sellacious\Import\ImportHelper;

/**
 * @package   Sellacious
 *
 * @since   1.5.2
 */
class ImporterController extends SellaciousControllerBase
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool   $cacheable  if true, the view output will be cached
	 * @param   mixed  $urlparams  An array of safe url parameters and their variable types,
	 *                             for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'import');
		$this->input->set('view', $view);

		// Todo: All components should not require to have this check embedded. Move this check to application context.
		if (!$this->helper->core->isRegistered() || !$this->helper->core->isConfigured())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));

			return $this;
		}
		elseif (!$this->canView())
		{
			$this->setRedirect(JRoute::_('index.php?option=com_sellacious'));
			$this->setMessage(JText::_('COM_IMPORTER_ACCESS_NOT_ALLOWED'), 'warning');

			return $this;
		}

		return parent::display($cacheable, $urlparams);
	}

	/**
	 * Checks whether a user can see this view.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function canView()
	{
		$view   = $this->input->get('view', 'import');
		$layout = $this->input->get('layout');
		$format = $this->input->get('format');
		$id     = $this->input->getInt('id', null);
		$basic  = array(
			'option' => 'com_importer',
			'view'   => $view,
			'layout' => $layout,
			'format' => $format,
		);
		$query  = $this->input->get->getArray();
		$query  = array_merge($basic, $query);

		if ($layout == 'edit')
		{
			$editId = $this->app->getUserState('com_importer.edit.' . $view . '.id');

			if ($id === null && $editId)
			{
				$query['id'] = $editId;

				// We should allow default edit id if not set in Request URI
				$this->app->redirect(JRoute::_('index.php?' . http_build_query($query), false));
			}

			if ($editId != $id)
			{
				/**
				 * Somehow the person just went to the form - we don't allow that.
				 * But instead of stopping him just switch the context
				 * if already editing something else, clear it from session to prevent data in new form
				 */
				$query['id'] = $id;
				$this->app->setUserState('com_importer.edit.' . $view . '.id', $id);
				$this->app->setUserState('com_importer.edit.' . $view . '.data', null);
				$this->app->redirect(JRoute::_('index.php?' . http_build_query($query), false));
			}
		}

		switch ($view)
		{
			case 'import':
				$handlers = ImportHelper::getHandlers();
				$allow    = $this->helper->access->check('importer.import', null, 'com_importer') && count($handlers) > 0;
				break;
			case 'imports':
				$allow = $this->canAccess('import', 'list');
				break;
			case 'templates':
				$allow = $this->canAccess('template', 'list');
				break;
			case 'template':
				$allow = $this->canAccess('template', 'edit');
				break;
			default:
				$allow = false;
		}

		return $allow;
	}

	/**
	 * Method to check whether the user can access the view
	 *
	 * @param   string  $asset
	 * @param   string  $type
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function canAccess($asset, $type = 'edit')
	{
		if ($type == 'edit')
		{
			$rules = array('.create', '.edit', '.edit.own');

			return $this->helper->access->checkAny($rules, $asset, '', 'com_importer');
		}
		elseif ($type == 'list')
		{
			$rules = array('.list', '.list.own');

			return $this->helper->access->checkAny($rules, $asset, '', 'com_importer');
		}

		return true;
	}
}
