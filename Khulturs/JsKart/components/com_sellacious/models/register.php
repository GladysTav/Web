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
 * Sellacious model.
 *
 * @since   1.2.0
 */
class SellaciousModelRegister extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
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

		$catid = $this->app->getUserStateFromRequest('com_sellacious.edit.register.catid', 'catid', 0, 'int');

		if ($catid)
		{
			$filters  = array('list.select' => 'a.id', 'id' => $catid, 'type' => 'client');
			$category = $this->helper->category->loadObject($filters);
		}
		else
		{
			$category = $this->helper->category->getDefault('client', 'a.id');
		}

		if ($category)
		{
			$this->state->set('register.catid', $category->id);

			$this->app->setUserState('com_sellacious.edit.register.catid', $category->id);
		}
		else
		{
			$this->app->setUserState('com_sellacious.edit.register.catid', null);

			$this->setError(JText::_('COM_SELLACIOUS_REGISTER_NO_CATEGORY_SELECTED'));
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getTable($type = 'User', $prefix = 'SellaciousTable', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 *
	 * @since   1.2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$name = strtolower($this->option . '.' . $this->name);

		$form = $this->loadForm($name, 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
		$catid = $this->getState('register.catid');

		$data['client']['category_id'] = $catid;

		$this->preprocessData('com_sellacious.' . $this->name, $data);

		return $data;
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
		$catid = $this->getState('register.catid');

		if ($catid)
		{
			$form->loadFile('profile/client');

			// Remove, why?
			$form->removeField('org_certificate', 'client');

			$fieldIds    = $this->helper->category->getFields(array($catid), array('core'), true, 'user');
			$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile', null, null,  null, 'basic');

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

		$form->setFieldAttribute('password', 'required', 'true');
		$form->setFieldAttribute('password2', 'required', 'true');

		if (!$this->helper->config->get('user_currency'))
		{
			$form->removeField('currency', 'profile');
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

		$catid = $this->getState('register.catid');
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
		$dispatcher->trigger('onContentBeforeSave', array('com_sellacious.register', $data, true));

		$custom   = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile  = ArrayHelper::getValue($postData, 'profile', array(), 'array');
		$client   = ArrayHelper::getValue($postData, 'client', array(), 'array');
		$address  = ArrayHelper::getValue($postData, 'address', array(), 'array');

		$registry = new Registry($postData);
		$registry->def('id', $this->getState($this->name . '.id'));
		$registry->def('username', $registry->get('email'));

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

		// Set up profile and all for the user just saved
		if ($profile)
		{
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

		$client['category_id'] = $this->getState('register.catid');
		$this->helper->user->addLinkedAccounts(array('client' => $client), $user->id);

		if ($address)
		{
			$address['is_primary'] = 1;

			$this->helper->user->saveAddress($address, $user->id);
		}

		$data['id'] = $user->id;

		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.register', (object) $data, true));

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
		$catid  = $this->getState('register.catid');

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

		if (!$visible->get('params.timezone'))
		{
			$form->removeField('timezone', 'params');
		}

		if (!$visible->get('profile.avatar'))
		{
			$form->removeField('avatar');
		}

		if (!$visible->get('client.client_type'))
		{
			$form->removeField('client_type', 'client');
		}

		if (!$visible->get('client.business_name'))
		{
			$form->removeField('business_name', 'client');
		}

		if (!$visible->get('client.org_reg_no'))
		{
			$form->removeField('org_reg_no', 'client');
		}

		if (!$visible->get('client.org_certificate'))
		{
			$form->removeField('org_certificate', 'client');
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

		if (!$visible->get('tnc') || !$me->guest)
		{
			$form->removeField('agree_tnc');
		}
		else
		{
			$form->setFieldAttribute('agree_tnc', 'catid', $catid);
		}

		// Special handling for address fields
		if ($visible->get('address'))
		{
			$form->loadFile('profile/address');

			$fields = array(
				'address',
				'landmark',
				'country',
				'state_loc',
				'district',
				'zip',
				'mobile',
				'company',
				'po_box',
				'residential',
			);

			foreach ($fields as $fieldName)
			{
				$show = $this->helper->config->get('geolocation_levels.' . $fieldName, 1);

				if ($show == 0)
				{
					$form->removeField($fieldName, 'address');
				}
				else
				{
					$form->setFieldAttribute($fieldName, 'required', $show == 2 ? 'true' : 'false', 'address');
				}
			}

			$showM = $this->helper->config->get('geolocation_levels.mobile', 1);
			$showZ = $this->helper->config->get('geolocation_levels.zip', 1);

			if ($showM && ($regexM = $this->helper->config->get('address_mobile_regex', '')))
			{
				$form->setFieldAttribute('mobile', 'validate', 'regex', 'address');
				$form->setFieldAttribute('mobile', 'regex', $regexM, 'address');
			}

			if ($showZ && ($regexZ = $this->helper->config->get('address_zip_regex', '')))
			{
				$form->setFieldAttribute('zip', 'validate', 'regex', 'address');
				$form->setFieldAttribute('zip', 'regex', $regexZ, 'address');
			}
		}
	}
}
