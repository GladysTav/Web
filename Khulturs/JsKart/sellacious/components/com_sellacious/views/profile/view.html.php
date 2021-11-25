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
 * View to edit a sellacious user account
 *
 * @since   1.2.0
 */
class SellaciousViewProfile extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'user';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'profile';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// If the user has access to full profile, let him to the 'user' view instead.
		$me = JFactory::getUser();

		if ($this->helper->access->check('user.edit'))
		{
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=user&layout=edit&id=' . $me->id, false));
		}
		// If the user has no access to profile, block access
		elseif ($me->guest || !$this->helper->access->check('user.edit.own'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check('user.edit.own'))
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');
		}
	}
}
