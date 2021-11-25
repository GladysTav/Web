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
defined('_JEXEC') or die;

/**
 * Shippingrule form view
 *
 * @since   1.0.0
 */
class SellaciousViewShippingRule extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'shippingrule';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'shippingrule';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'shippingrules';

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		if ($isNew ? $this->helper->access->check($this->action_prefix . '.create') : $this->helper->access->checkAny(array('edit', 'edit.own'), $this->action_prefix . '.'))
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');

			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');

			if ($this->helper->access->check($this->action_prefix . '.create'))
			{
				JToolBarHelper::custom($this->view_item . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);

				if (!$isNew)
				{
					JToolBarHelper::custom($this->view_item . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
