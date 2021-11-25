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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Utilities\Otp;

/**
 * Sellacious seller model.
 *
 * @since   1.2.0
 */
class SellaciousModelSeller extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return false;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return false;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$catid = $this->app->getUserStateFromRequest('com_sellacious.edit.seller.catid', 'catid', 0, 'int');

		if ($catid)
		{
			$filters  = array('list.select' => 'a.id', 'id' => $catid, 'type' => 'seller');
			$category = $this->helper->category->loadObject($filters);
		}
		else
		{
			$category = $this->helper->category->getDefault('seller', 'a.id');
		}

		if ($category)
		{
			$this->state->set('seller.catid', $category->id);

			$this->app->setUserState('com_sellacious.edit.seller.catid', $category->id);
		}
		else
		{
			$this->app->setUserState('com_sellacious.edit.seller.catid', null);

			$this->setError(JText::_('COM_SELLACIOUS_SELLER_REGISTER_NO_CATEGORY_SELECTED'));
		}

		$me = JFactory::getUser();

		if (!$me->guest)
		{
			$this->state->set('seller.id', $me->id);
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data  = $this->app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');
		$catid = $this->getState('seller.catid');

		$data['seller']['category_id'] = $catid;

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		$me = JFactory::getUser();

		if (!$me->guest)
		{
			if (is_object($data))
			{
				$data->name     = $me->name;
				$data->email    = $me->email;
				$data->username = $me->username;
			}
			elseif (is_array($data))
			{
				$data['name']     = $me->name;
				$data['email']    = $me->email;
				$data['username'] = $me->username;
			}
		}

		parent::preprocessData($context, $data, $group);
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin group to load
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$me    = JFactory::getUser();
		$catid = $this->getState('seller.catid');

		if (!$me->guest)
		{
			$form->setFieldAttribute('name', 'disabled', 'true');
			$form->setFieldAttribute('email', 'disabled', 'true');
			$form->setFieldAttribute('name', 'required', 'false');
			$form->setFieldAttribute('email', 'required', 'false');
			$form->setFieldAttribute('avatar', 'recordId', $me->id);
		}

		if (!$catid)
		{
			$form->removeGroup('seller');
		}
		else
		{
			$fieldIds    = $this->helper->category->getFields(array($catid), array('core'), true, 'user');
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile', null, null, null, 'basic');

			foreach ($xmlElements as $xmlElement)
			{
				$form->load($xmlElement);
			}

			$checkE = $this->helper->category->getCategoryParam($catid, 'preverify.email', 0, true);
			$checkM = $this->helper->category->getCategoryParam($catid, 'preverify.mobile', 0, true);

			if ($checkE)
			{
				$form->setFieldAttribute('email', 'type', 'preverify');
			}

			if ($checkM)
			{
				$form->setFieldAttribute('mobile', 'type', 'preverify', 'profile');
			}
		}

		// Remove disabled fields from form
		$this->removeFields($form);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 *
	 * @since   2.0.0
	 */
	public function validate($form, $data, $group = null)
	{
		$registry = new Registry($data);

		$actE = $this->parseVerified($registry, 'email', 'preverify.email');

		if ($actE === false)
		{
			$this->setError(JText::_('COM_SELLACIOUS_PROFILE_EMAIL_VERIFICATION_REQUIRED'));
		}

		$actM = $this->parseVerified($registry, 'profile.mobile', 'preverify.mobile');

		if ($actM === false)
		{
			$this->setError(JText::_('COM_SELLACIOUS_PROFILE_MOBILE_VERIFICATION_REQUIRED'));
		}

		$arr   = $registry->toArray();
		$valid = parent::validate($form, $arr, $group);

		if (!$valid || $actE === false || $actM === false)
		{
			return false;
		}

		$registry = new Registry($valid);

		if ($actE === true)
		{
			$registry->set('params.preverify.verified.email', true);
		}

		if ($actM === true)
		{
			$registry->set('params.preverify.verified.mobile', true);
		}

		return $registry->toArray();
	}

	protected function parseVerified(Registry $registry, $key, $conf)
	{
		$hash = $registry->get($key);

		if (!$hash)
		{
			return null;
		}

		$catid = $this->getState('seller.catid');
		$check = $this->helper->category->getCategoryParam($catid, $conf, 0, true);

		if (!$check)
		{
			return null;
		}

		$value = Otp::secureUnhash($hash);

		if ($value)
		{
			$registry->set($key, $value);

			return true;
		}

		return false;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  int  The user id
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$postData = $data;

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.seller', $data, true));

		$custom  = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile = ArrayHelper::getValue($postData, 'profile', array(), 'array');
		$seller  = ArrayHelper::getValue($postData, 'seller', array(), 'array');

		$registry = new Registry($postData);
		$registry->def('id', $this->getState($this->name . '.id'));
		$registry->def('username', $registry->get('email'));

		if (JFactory::getUser()->guest)
		{
			try
			{
				$activate = $registry->get('params.preverify.verified.email') || $registry->get('params.preverify.verified.mobile');
				$user     = $this->helper->user->autoRegister($registry, $activate);

				if (!$user instanceof JUser)
				{
					return false;
				}

				$this->setState($this->name . '.id', $user->id);
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$user = JUser::getInstance($registry->get('id'));
		}

		try
		{
			$_control    = 'jform.avatar';
			$_tableName  = 'user';
			$_context    = 'avatar';
			$_recordId   = $user->id;
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = ArrayHelper::getValue($data, 'avatar', array(), 'array');

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'warning');
		}

		// Set up profile and all for the user just saved
		if ($profile)
		{
			$oProfile = (array) $this->helper->profile->getItem(array('user_id' => $user->id));
			$profile  = array_merge($oProfile, $profile);
			$this->helper->user->saveProfile($profile, $user->id);
		}
		else
		{
			$this->helper->profile->create($user->id);
		}

		if ($custom)
		{
			$this->helper->user->saveCustomProfile($user->id, (array) $custom);
		}

		$seller['category_id'] = $this->getState('seller.catid');
		$this->helper->user->addLinkedAccounts(array('seller' => $seller), $user->id);

		$data['id'] = $user->id;

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.seller', (object) $data, true));

		return $user->id;
	}

	/**
	 * Method to remove fields from the form
	 *
	 * @param   JForm  $form  The target registration form instance
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function removeFields(JForm $form)
	{
		$me     = JFactory::getUser();
		$catid  = $this->getState('seller.catid');

		// Inherited attributes was not used here, fix this in another PR
		$filters = array('list.select' => 'a.params', 'id' => $catid);
		$params  = $this->helper->category->loadResult($filters);
		$params  = new Registry($params);
		$params  = $params->extract('profile_fields');
		$visible = $params ? $params->get('register') : null;

		// Separator '.' conflicts with registry path, use JObject instead of Registry
		$visible = new JObject($visible);

		if (count($visible->getProperties()) === 0)
		{
			$visible->set('name', 1);
			$visible->set('email', 1);
			$visible->set('profile.mobile', 1);
			$visible->set('password', 1);
		}

		// Email can only be hidden if mobile is shown, can't hide both
		if (!$visible->get('profile.mobile'))
		{
			$visible->set('email', 1);
		}

		if (!$visible->get('name'))
		{
			$form->removeField('name');
		}

		if (!$visible->get('email'))
		{
			$form->removeField('email');
			$form->setFieldAttribute('mobile', 'required', 'true', 'profile');
		}

		if (!$visible->get('password'))
		{
			$form->removeField('password');
			$form->removeField('password2');
		}

		if (!$visible->get('username'))
		{
			$form->removeField('username');
		}
		elseif (!$me->guest)
		{
			$form->setFieldAttribute('username', 'readonly', 'true');
		}

		if (!$visible->get('params.timezone'))
		{
			$form->removeField('timezone', 'params');
		}

		if (!$visible->get('seller.title'))
		{
			$form->removeField('title', 'seller');
		}

		if (!$visible->get('seller.avatar'))
		{
			$form->removeField('avatar');
		}

		if (!$visible->get('seller.store_name'))
		{
			$form->removeField('store_name', 'seller');
		}

		if (!$visible->get('seller.store_address'))
		{
			$form->removeField('store_address', 'seller');
		}

		if (!$visible->get('seller.currency'))
		{
			$form->removeField('currency', 'seller');
		}

		if (!$visible->get('seller.store_location'))
		{
			$form->removeField('store_location', 'seller');
		}

		if ($visible->get('seller.params.social'))
		{
			$form->loadFile('profile/seller/social');
		}

		if (!$visible->get('profile.mobile'))
		{
			$form->removeField('mobile', 'profile');
		}

		if (!$visible->get('profile.website'))
		{
			$form->removeField('website', 'profile');
		}

		if (!$visible->get('profile.currency'))
		{
			$form->removeField('currency', 'profile');
		}

		// We also show for registered users as seller registration is only shown for non-sellers
		if (!$visible->get('tnc'))
		{
			$form->removeField('agree_tnc');
		}
		else
		{
			$form->setFieldAttribute('agree_tnc', 'catid', $catid);
		}
	}
}
