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
use Sellacious\Access\AccessHelper;
use Sellacious\Toolbar\Button\ConfirmButton;
use Sellacious\Toolbar\Toolbar;
use Sellacious\Toolbar\ToolbarHelper;
use Sellacious\User\UserGroupHelper;

defined('_JEXEC') or die;

/**
 * View to edit
 *
 * @since   1.2.0
 */
class SellaciousViewPermissions extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'permissions';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'permissions';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = null;

	/**
	 * Method to prepare data/view before rendering the display. Child classes can override this to alter view object
	 * before actual display is called.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
		$this->setLayout('edit');

		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.2.0
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if (AccessHelper::allow('permissions.edit'))
		{
			/** @var  Joomla\Registry\Registry  $registry */
			$toolbar  = Toolbar::getInstance();
			$registry = $this->get('form')->getData();
			$group    = UserGroupHelper::get($registry->get('user_group', 1));
			$assetId  = AccessHelper::getAssetId($registry->get('component'));
			$inherit  = $group->level > 0 && $assetId != 1;

			if ($inherit)
			{
				$toolbar->appendButton(new ConfirmButton('COM_SELLACIOUS_PERMISSIONS_CONFIRM_SET_ALL_INHERIT', 'file-remove', 'COM_SELLACIOUS_CATEGORY_FIELD_SET_ALL_INHERIT', 'permissions.clearAll', false));
			}
			else
			{
				$toolbar->appendButton(new ConfirmButton('COM_SELLACIOUS_PERMISSIONS_CONFIRM_SET_ALL_DISALLOW', 'eye-blocked', 'COM_SELLACIOUS_CATEGORY_FIELD_SET_ALL_DISALLOW', 'permissions.clearAll', false));
			}

			$toolbar->appendButton(new ConfirmButton('COM_SELLACIOUS_PERMISSIONS_CONFIRM_SET_ALL_ALLOWED', 'checkmark', 'COM_SELLACIOUS_CATEGORY_FIELD_SET_ALL_ALLOWED', 'permissions.allowAll', false));
			$toolbar->appendButton(new ConfirmButton('COM_SELLACIOUS_PERMISSIONS_CONFIRM_SET_ALL_DENIED', 'remove', 'COM_SELLACIOUS_CATEGORY_FIELD_SET_ALL_DENIED', 'permissions.denyAll', false));

			$toolbar->appendButton(new ConfirmButton('COM_SELLACIOUS_PERMISSIONS_CONFIRM_DEFAULT_RESET', 'undo', 'COM_SELLACIOUS_CATEGORY_FIELD_ACCESS_DEFAULT_RESET', 'permissions.reset', false));

			ToolBarHelper::apply('permissions.apply', 'JTOOLBAR_APPLY');
		}
	}
}
