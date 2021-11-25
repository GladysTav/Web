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
 * View to edit a template
 *
 * @since   1.5.2
 */
class ImporterViewTemplate extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $action_prefix = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_item = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_list = 'templates';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.5.2
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		$me        = JFactory::getUser();
		$isNew     = ($this->item->get('id') == 0);
		$createdBy = $this->item->get('created_by');
		$allowEdit = $isNew ? $this->helper->access->check($this->action_prefix . '.create', null, 'com_importer') : ($this->helper->access->check($this->action_prefix . '.edit', null, 'com_importer') ||
			($this->helper->access->check($this->action_prefix . '.edit.own', null, 'com_importer') && $createdBy == $me->id));

		if ($allowEdit)
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');

			if (!$isNew && $this->helper->access->check($this->action_prefix . '.copy', null, 'com_importer'))
			{
				JToolBarHelper::custom($this->view_item . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', 'JTOOLBAR_CLOSE');
	}
}
