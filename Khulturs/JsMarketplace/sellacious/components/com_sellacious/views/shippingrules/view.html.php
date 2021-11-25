<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;
use Sellacious\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for a list of shippingrules.
 *
 */
class SellaciousViewShippingRules extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'shippingrule';

	/** @var  string */
	protected $view_item = 'shippingrule';

	/** @var  string */
	protected $view_list = 'shippingrules';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.7.0
	 */
	protected function addToolbar()
	{
		$me    = JFactory::getUser();
		$state = $this->get('State');

		$this->setPageTitle();

		$toolbar = Toolbar::getInstance();

		$editable      = file_exists(JPATH_COMPONENT . '/views/' . $this->view_item);
		$createAllowed = true;

		if (!$me->authorise('core.admin') && $this->helper->seller->is() && ($this->helper->config->get('shipped_by', 'shop') == 'shop' || $this->helper->config->get('itemised_shipping', 0) == 0))
		{
			$createAllowed = false;
		}

		if ($editable && $this->helper->access->check($this->action_prefix . '.create') && $createAllowed)
		{
			$toolbar->appendButton(new StandardButton('new', 'JTOOLBAR_NEW', $this->view_item . '.add', false));
		}

		$gState = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
		$toolbar->appendGroup($gState);

		if (count($this->items))
		{
			if ($this->helper->access->check($this->action_prefix . '.edit.state'))
			{
				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '1')
				{
					$gState->appendButton(new StandardButton('publish', 'JTOOLBAR_PUBLISH', $this->view_list . '.publish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '0')
				{
					$gState->appendButton(new StandardButton('unpublish', 'JTOOLBAR_UNPUBLISH', $this->view_list . '.unpublish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '-2')
				{
					$gState->appendButton(new StandardButton('trash', 'JTOOLBAR_TRASH', $this->view_list . '.trash', true));
				}
				// If 'edit.state' is granted, then show 'delete' only if filtered on 'trashed' items
				elseif ($state->get('filter.state') == '-2' && $this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
				{
					ToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
				}
			}
			// We can allow direct 'delete' implicitly for his (seller) own items if so permitted.
			elseif ($this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
			{
				ToolBarHelper::trash($this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($this->is_nested && $this->helper->access->check('core.admin'))
		{
			ToolBarHelper::custom($this->view_list . '.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}
	}
}
