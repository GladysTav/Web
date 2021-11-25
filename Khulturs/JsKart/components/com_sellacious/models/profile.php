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
class SellaciousModelProfile extends SellaciousModelAdmin
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
		parent::populateState();

		$me  = JFactory::getUser();

		// This form view is for user's own profile only with restricted access.
		$this->app->setUserState('com_sellacious.edit.profile.id', $me->id);
		$this->state->set('profile.id', $me->id);

		if ($this->app->input->get('layout') == 'edit')
		{
			$this->state->set('profile.layout', 'edit');
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
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = $this->app->getUserStateFromRequest("$this->option.edit.$this->name.data", 'jform', array(), 'array');

		if (empty($data))
		{
			// Load user info
			$data = $this->getItem();

			// Remove password
			unset($data->password);

			$registry   = new Registry($data);
			$categories = array($registry->get('client.category_id'), $registry->get('seller.category_id'));

			// Hash only if loading from db
			if ($this->state->get('profile.layout') == 'edit' && $this->isVerify($categories, 'preverify.email'))
			{
				$data->email = Otp::secureHash($data->email);
			}

			if ($this->state->get('profile.layout') == 'edit' && $this->isVerify($categories, 'preverify.mobile') && isset($data->profile->mobile))
			{
				$data->profile->mobile = Otp::secureHash($data->profile->mobile);
			}
		}

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
		$registry = new Registry($data);
		$userId   = $this->getState('profile.id');
		$clientId = $registry->get('client.id');
		$catidC   = $registry->get('client.category_id');
		$catidS   = $registry->get('seller.category_id');

		if (!$catidC)
		{
			$category = $this->helper->category->getDefault('client', 'a.id');
			$catidC   = $category ? $category->id : 0;

			$registry->set('client.category_id', $catidC);
		}

		$form->loadFile('profile/client');

		if ($catidS)
		{
			$form->loadFile('profile/seller');
		}

		$form->setFieldAttribute('org_certificate', 'recordId', $clientId, 'client');
		$form->setFieldAttribute('avatar', 'recordId', $userId);

		// Remove Profile Currency if not allowed in Global Configuration for Client
		if (!$this->helper->config->get('user_currency'))
		{
			$form->removeField('currency', 'profile');
		}

		$fieldIds    = $this->helper->category->getFields(array_filter(array($catidC, $catidS)), array('core'), true, 'user');
		$xmlElements = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile', null, null, null, 'basic');

		foreach ($xmlElements as $xmlElement)
		{
			$form->load($xmlElement);
		}

		if ($this->isVerify(array($catidC, $catidS), 'preverify.email'))
		{
			$form->setFieldAttribute('email', 'type', 'preverify');
		}

		if ($this->isVerify(array($catidC, $catidS), 'preverify.mobile'))
		{
			$form->setFieldAttribute('mobile', 'type', 'preverify', 'profile');
		}

		$this->removeFields($form, $catidC, $catidS);

		// Username only for registration
		$form->setFieldAttribute('username', 'readonly', 'true');

		// No T&C in edit profile
		$form->removeField('agree_tnc');

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

		$catidC = $this->helper->client->getCategory($registry->get('id'), true);
		$catidS = $this->helper->seller->getCategory($registry->get('id'));
		$check  = $this->isVerify(array($catidC, $catidS), $conf);

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
	 * @return  int
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		// Extract variables
		$custom  = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile = ArrayHelper::getValue($data, 'profile', null);
		$seller  = ArrayHelper::getValue($data, 'seller', null);
		$client  = ArrayHelper::getValue($data, 'client', null);
		$address = ArrayHelper::getValue($data, 'address', null);

		unset($data['custom_profile'], $data['profile'], $data['seller'], $data['client'], $data['address']);

		$isNew = empty($data['id']);

		$data['id'] = $this->getState('profile.id');

		$user = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
		$this->helper->user->saveProfile($profile, $user->id);

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

		if ($custom)
		{
			$this->helper->user->saveCustomProfile($user->id, (array) $custom);
		}

		// save seller info
		if (!empty($seller))
		{
			$this->helper->user->addLinkedAccounts(array('seller' => $seller), $user->id);
		}

		// Save client
		if (!empty($client['category_id']))
		{
			$client['user_id'] = $user->id;

			$cId = $this->helper->user->addAccount($client, 'client');

			try
			{
				$_control    = 'jform.client.org_certificate';
				$_tableName  = 'clients';
				$_context    = 'org_certificate';
				$_recordId   = $cId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx');
				$_options    = ArrayHelper::getValue($client, 'org_certificate', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}
		}

		if ($address)
		{
			$address['is_primary'] = 1;

			$this->helper->user->saveAddress($address, $user->id);
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.profile', $user, $isNew));

		return $user->id;
	}

	/**
	 * Save the user record
	 *
	 * @param   array  $data  The data to save for related Joomla user account.
	 *
	 * @return  JUser|bool  The user id of the user account on success, false otherwise
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function saveUser($data)
	{
		$user = JUser::getInstance($data['id']);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('sellacious');

		// Bind the data.
		if (!$user->bind($data))
		{
			$this->setError($user->getError());

			return false;
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger('onBeforeSaveUser', array($this->option . '.' . $this->name, &$user, false));

		// Store the data.
		if (!$user->save())
		{
			$this->setError($user->getError());

			return false;
		}

		// Trigger the onAfterSave event.
		$dispatcher->trigger('onAfterSaveUser', array($this->option . '.' . $this->name, &$user, false));

		return $user;
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   JObject  $item
	 *
	 * @return  JObject
	 *
	 * @since   1.6.0
	 */
	protected function processItem($item)
	{
		$item = parent::processItem($item);
		$pk   = $item->get('id');

		$profile = $this->helper->profile->getItem(array('user_id' => $pk));
		$seller  = $this->helper->seller->getItem(array('user_id' => $pk));
		$client  = $this->helper->client->getItem(array('user_id' => $pk));
		$address = $this->helper->user->getPrimaryAddress($pk);
		$custom  = $this->helper->field->getValue('profile', $pk);

		if (isset($seller->params))
		{
			$seller->params = json_decode($seller->params);
		}

		$customProfileData = $this->helper->field->buildData($custom);

		$item->set('profile', $profile);
		$item->set('seller', $seller);
		$item->set('client', $client);
		$item->set('address', $address);
		$item->set('custom_profile', $custom);
		$item->set('custom_profile_data', $customProfileData);

		return $item;
	}

	/**
	 * Method to remove fields from profile edit form
	 *
	 * @param   JForm  $form    The form instance
	 * @param   int    $catidC  The client category id
	 * @param   int    $catidS  The seller category id
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function removeFields(JForm $form, $catidC, $catidS)
	{
		$visible = $this->getShowOptions(array($catidC, $catidS));

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

		if (!$visible->get('params.timezone'))
		{
			$form->removeField('timezone', 'params');
		}

		if (!$visible->get('seller.title'))
		{
			$form->removeField('title', 'seller');
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
	}

	/**
	 * Get visible fields array from config
	 *
	 * @param   int  $catid  The category id
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function getVisible($catid)
	{
		// Inherited attributes was not used here, fix this in another PR
		$filters = array('list.select' => 'a.params', 'id' => $catid);
		$params  = $catid ? $this->helper->category->loadResult($filters) : null;
		$params  = $params ? new Registry($params) : null;
		$params  = $params ? $params->extract('profile_fields') : null;

		return $params ? $params->get('profile') : null;
	}

	/**
	 * Get visible fields array from config
	 *
	 * @param   int[]  $categories  The user category ids array
	 *
	 * @return  JObject
	 *
	 * @since   2.0.0
	 */
	public function getShowOptions($categories)
	{
		// Separator '.' conflicts with registry path, use JObject instead of Registry
		$visible = new JObject;

		foreach ($categories as $cid)
		{
			$visible->setProperties($this->getVisible($cid));
		}

		return $visible;
	}

	/**
	 * Whether to validate opt/mobile or not
	 *
	 * @param   array   $categories
	 * @param   string  $confKey
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function isVerify(array $categories, $confKey)
	{
		$check = true;

		foreach ($categories as $catid)
		{
			if ($catid)
			{
				// If one category says YES then YES, else if any says NO then NO, otherwise YES!
				if ($this->helper->category->getCategoryParam($catid, $confKey, 0, true))
				{
					return true;
				}

				$check = false;
			}
		}

		return $check;
	}
}
