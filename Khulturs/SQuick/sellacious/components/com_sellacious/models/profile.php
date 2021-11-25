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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

/**
 * Sellacious model.
 *
 * @since   1.0.0
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

		$uid = JFactory::getUser()->id;

		// This form view is for user's own profile only with restricted access.
		$this->app->setUserState('com_sellacious.edit.profile.id', $uid);
		$this->state->set('profile.id', $uid);
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
	 * @since   1.1.0
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

			// Add profile info
			$profile = $this->helper->profile->getItem(array('user_id' => $data->get('id')));
			$custom  = $this->helper->field->getValue('profile', $data->get('id'));

			$data->set('profile', $profile);
			$data->set('custom_profile', $custom);

			// This is the form data so we only load active records
			$accounts = $this->helper->user->getLinkedAccounts($data->get('id'), true);

			foreach ($accounts as $type => $account)
			{
				// Load seller shippable locations also
				if ($type == 'seller' && !empty($account))
				{
					$account->shipping_geo = $this->helper->seller->getShipLocations($data->get('id'), true);
				}

				$data->set($type, $account);
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
	 * @since   1.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$config   = ConfigHelper::getInstance('com_sellacious');
		$registry = new Registry($data);

		$catIdC = $registry->get('client.category_id');
		$catIdS = $registry->get('seller.category_id');
		$catIdM = $registry->get('manufacturer.category_id');
		$catIdF = $registry->get('staff.category_id');

		if (!$catIdC)
		{
			$cCatD  = $this->helper->category->getDefault('client', 'a.id');
			$catIdC = $cCatD ? $cCatD->id : 0;
		}

		if ($catIdC)
		{
			$form->loadFile('profile/client');

			$form->setFieldAttribute('org_certificate', 'recordId', $registry->get('client.id'), 'client');

			if (!$this->helper->access->isSubscribed() || !$config->get('allow_client_authorised_users'))
			{
				// We are not using authorised users, why keeping this?
				$form->removeField('authorised', 'client');
			}
		}

		if ($catIdS)
		{
			$form->loadFile('profile/seller');

			$form->setFieldAttribute('logo', 'recordId', $registry->get('seller.id'), 'seller');

			if ($config->get('shipped_by') != 'seller')
			{
				$form->removeField('ship_origin_group', 'seller');
				$form->removeField('ship_origin_country', 'seller');
				$form->removeField('ship_origin_state', 'seller');
				$form->removeField('ship_origin_district', 'seller');
				$form->removeField('ship_origin_zip', 'seller');
				$form->removeField('ship_origin_address_line1', 'seller');
				$form->removeField('ship_origin_address_line2', 'seller');
				$form->removeField('ship_origin_address_line3', 'seller');
			}

			if (!$config->get('listing_currency'))
			{
				$form->removeField('currency', 'seller');
			}

			if (!$config->get('shippable_location_by_seller'))
			{
				$form->removeGroup('seller.shipping_geo');
			}
		}

		if ($catIdM)
		{
			$form->loadFile('profile/manufacturer', true);

			$form->setFieldAttribute('logo', 'recordId', $registry->get('manufacturer.id'), 'manufacturer');
		}

		if ($catIdF)
		{
			$form->loadFile('profile/staff');
		}

		$form->loadFile('profile/address');

		$form->setFieldAttribute('avatar', 'recordId', JFactory::getUser()->id);

		if (!$config->get('user_currency'))
		{
			$form->removeField('currency', 'profile');
		}

		$categories = array_filter(array($catIdC, $catIdS, $catIdF, $catIdM));
		$fieldIds   = $this->helper->category->getFields($categories, array('core'), true, 'user');
		$elements   = $this->helper->field->getFieldsXML($fieldIds, 'custom_profile', null, null, null, 'basic');

		foreach ($elements as $element)
		{
			$form->load($element);
		}

		$this->removeFields($form, $catIdC, $catIdS);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to return a single record. Joomla model doesn't use caching, we use.
	 *
	 * @param   JObject  $item  The record item.
	 *
	 * @return  JObject
	 *
	 * @since   1.2.0
	 */
	public function processItem($item)
	{
		if ($user_id = $item->get('id'))
		{
			$item->set('addresses', $this->helper->user->getAddresses($user_id));
		}

		return $item;
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
	 * @since   1.0.0
	 */
	public function save($data)
	{
		// Extract variables
		$custom       = ArrayHelper::getValue($data, 'custom_profile', null);
		$profile      = ArrayHelper::getValue($data, 'profile', null);
		$manufacturer = ArrayHelper::getValue($data, 'manufacturer', null);
		$seller       = ArrayHelper::getValue($data, 'seller', null);
		$staff        = ArrayHelper::getValue($data, 'staff', null);
		$client       = ArrayHelper::getValue($data, 'client', null);

		unset($data['custom_profile'], $data['profile'], $data['manufacturer'], $data['seller'], $data['staff'], $data['client']);

		$user = $this->saveUser($data);

		if (!($user instanceof JUser))
		{
			return false;
		}

		// Set up profile and all for the user just saved
		$this->helper->user->saveProfile($profile, $user->id);
		$this->helper->user->saveCustomProfile($user->id, (array) $custom);

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

		// Save manufacturer
		if (!empty($manufacturer['category_id']))
		{
			$manufacturer['user_id'] = $user->id;

			$mId = $this->helper->user->addAccount($manufacturer, 'manufacturer');

			try
			{
				$_control    = 'jform.manufacturer.logo';
				$_tableName  = 'manufacturers';
				$_context    = 'logo';
				$_recordId   = $mId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif');
				$_options    = ArrayHelper::getValue($manufacturer, 'logo', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'manufacturer');
		}

		// Save seller
		if (!empty($seller['category_id']))
		{
			$seller['user_id'] = $user->id;

			$locations = ArrayHelper::getValue($seller, 'shipping_geo', array(), 'array');
			$locations = explode(',', implode(',', $locations));

			$sId = $this->helper->user->addAccount($seller, 'seller');

			try
			{
				$_control    = 'jform.seller.logo';
				$_tableName  = 'sellers';
				$_context    = 'logo';
				$_recordId   = $sId;
				$_extensions = array('jpg', 'png', 'jpeg', 'gif');
				$_options    = ArrayHelper::getValue($seller, 'logo', array(), 'array');

				$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'warning');
			}

			$this->helper->seller->setShipLocations($user->id, $locations);
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'seller');
			$this->helper->seller->setShipLocations($user->id, array());
		}

		// Save staff
		if (!empty($staff['category_id']))
		{
			$staff['user_id'] = $user->id;

			$this->helper->user->addAccount($staff, 'staff');
		}
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'staff');
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
		else
		{
			// Remove from existing
			$this->helper->user->removeAccount($user->id, 'client');
		}

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.user', $user, false));

		return $user->id;
	}

	/**
	 * @param   array  $data  The data to save for related Joomla user account.
	 *
	 * @return  JUser|bool  The user id of the user account on success, false otherwise
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function saveUser($data)
	{
		$pk = !empty($data['id']) ? $data['id'] : (int) $this->getState($this->name . '.id');

		if ($pk == 0)
		{
			$registry = new Registry($data);
			$user     = $this->helper->user->autoRegister($registry);

			// Set global edit id in case rest of the process fails, page should load with new user id
			// Joomla bug in Registry, array key does not update. Fixed in later version of J! 3.4.x
			$state       = $this->app->getUserState("com_sellacious.edit.$this->name.data");
			$state['id'] = $user->id;

			$this->setState("$this->name.id", $user->id);
			$this->app->setUserState("com_sellacious.edit.$this->name.data", $state);
			$this->app->setUserState("com_sellacious.edit.$this->name.id", (int) $user->id);
		}
		else
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
		}

		return $user;
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
		// Separator '.' conflicts with registry path, use JObject instead of Registry
		$visible = new JObject;
		$visible->setProperties($this->getVisible($catidC));
		$visible->setProperties($this->getVisible($catidS));

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

		// Special handling for address fields
		// (@20200128@ - Address fields handling now removed because its already handled in SellaciousHelperUser::sortFields and validate field is already set)
		if (!$visible->get('address'))
		{
			$form->removeGroup('address');
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
}
