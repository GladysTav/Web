<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;
use Sellacious\Language\LanguageText;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 */
class SellaciousModelConfig extends SellaciousModelAdmin
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @note   Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering
	 * @param   string  $direction
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$this->app->getUserStateFromRequest('com_sellacious.config.return', 'return', '', 'cmd');

		parent::populateState();
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function save($data)
	{
		$jconfig   = ArrayHelper::getValue($data, 'jconfig');
		$languages = ArrayHelper::getValue($data, 'language');
		unset($data['jconfig']);
		unset($data['tags']);
		unset($data['language']);

		$dispatcher = $this->helper->core->loadPlugins();

		foreach ($data as $name => $params)
		{
			$this->helper->config->save($params, $name);
		}

		if (!empty($languages))
		{
			$languageC = JFactory::getLanguage();
			$tag       = $languageC->getTag();

			JModelLegacy::addIncludePath(JPATH_SELLACIOUS . '/components/com_languages/models');
			/** @var  LanguagesModelStrings  $model */
			$model = JModelLegacy::getInstance('Strings', 'LanguagesModel');

			foreach ($languages as $code => $value)
			{
				$query = $this->_db->getQuery(true)->select('a.id')->from($this->_db->qn('#__languages_strings', 'a'))->where('a.lang_constant = ' . $this->_db->q($code));
				$this->_db->setQuery($query);

				$id = $this->_db->loadResult();

				if (!$id)
				{
					$values = array($code, $value, 'site', 'com_sellacious', $tag . '.com_sellacious.ini');

					$query->insert($this->_db->qn('#__languages_strings'))
						->columns($this->_db->qn(array('lang_constant', 'orig_text', 'client', 'extension', 'filename')));
					$query->values(implode(', ', $this->_db->q($values)));

					$this->_db->setQuery($query)->execute();

					$id = $this->_db->insertid();
				}

				$model->setValue($id, $value, $tag);
			}
		}

		$this->helper->config->saveJConfig($jconfig);
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.config', (object) $data, false));

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  stdClass
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// Todo: This should load all configurations which are allowed to edit here
		$params = $this->helper->config->getParams();

		if (!$this->helper->access->isSubscribed())
		{
			$params->set('show_brand_footer', 1);
			$params->set('show_rate_us', 1);
			$params->set('show_doc_link', 1);
			$params->set('show_support_link', 1);
			$params->set('show_advertisement', 1);
			$params->set('show_back_to_joomla', 1);
			$params->set('show_sellacious_version', 1);
			$params->set('show_license_to', 1);
			$params->set('enable_fe_messages', 0);
			$params->set('dashboard_show_statistics', 1);
		}

		$fields = $this->helper->field->getGlobalFields('checkoutform');
		$params->set('checkoutform_global', $fields);

		$data = (object) array('com_sellacious' => $params->toArray());

		// Languages
		$this->setLanguages($data);

		// JConfig data
		$config        = new JConfig();
		$data->jconfig = (array) $config;

		return $data;
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form
	 * @param   mixed   $data
	 * @param   string  $group
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$this->helper->core->loadPlugins();

		$form->loadFile('config/shop');
		$form->loadFile('config/prices');
		$form->loadFile('config/premium');
		$form->loadFile('config/layout');
		$form->loadFile('config/layout_admin');
		$form->loadFile('config/preset');
		$form->loadFile('config/sef');
		$form->loadFile('config/seo');
		$form->loadFile('config/media');
		$form->loadFile('config/registration');
		$form->loadFile('config/seller');
		$form->loadFile('config/shipment');
		$form->loadFile('config/rating');
		$form->loadFile('config/b2b');

		// This will be in trigger of the module after "events" plugin is merged in core
		JFactory::getLanguage()->load('mod_sellacious_filters.sys', JPATH_SITE . '/modules/mod_sellacious_filters');
		JFactory::getLanguage()->load('mod_sellacious_filters.sys', JPATH_SITE);

		$form->loadFile(JPATH_SITE . '/modules/mod_sellacious_filters/config.xml');

		if (!$this->helper->access->isSubscribed())
		{
			$form->setFieldAttribute('backoffice_logo', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logo', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logoicon', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_logoicon', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('backoffice_favicon', 'disabled', 'true', 'com_sellacious');
			$form->setFieldAttribute('backoffice_favicon', 'limit', '1', 'com_sellacious');
			$form->setFieldAttribute('show_brand_footer', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_rate_us', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_doc_link', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_support_link', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_advertisement', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_back_to_joomla', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_sellacious_version', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('show_license_to', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('enable_fe_messages', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('allow_client_authorised_users', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('allow_credit_limit', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('login_to_see_price', 'readonly', 'true', 'com_sellacious');
			$form->setFieldAttribute('dashboard_show_statistics', 'readonly', 'true', 'com_sellacious');
		}

		JText::script('COM_SELLACIOUS_CONFIG_REFRESH_RATING_CACHE');
		JText::script('COM_SELLACIOUS_CONFIG_RATING_CACHE_REFRESHING');
		JText::script('COM_SELLACIOUS_CONFIG_RATING_REFRESH_SUCCESS');

		$spacer = '<a class="btn btn-primary btn-refresh-rating-cache" href="#">' . JText::_('COM_SELLACIOUS_CONFIG_REFRESH_RATING_CACHE') . '</a>';
		$form->setFieldAttribute('refresh_rating_cache', 'label', $spacer, 'com_sellacious');

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to set languages data
	 *
	 * @param   \stdClass  $data  The config form data
	 *
	 * @since   1.7.0
	 */
	public function setLanguages($data)
	{
		$data->language = new stdClass();
		$languageC      = JFactory::getLanguage();
		$tag            = $languageC->getTag();

		$query = $this->_db->getQuery(true)->select('a.*')->from($this->_db->qn('#__languages_strings', 'a'))->where('a.lang_constant = ' . $this->_db->q('COM_SELLACIOUS_CART_SHIPMENT_STATUS_TBD_DISCLAIMER'));
		$this->_db->setQuery($query);
		$string = $this->_db->loadObject();

		if ($string)
		{
			$language = new LanguageText($tag);
			$clients  = JApplicationHelper::getClientInfo();
			$clients  = ArrayHelper::pivot($clients, 'name');
			$client   = ArrayHelper::getValue($clients, $string->client);

			if ($client)
			{
				$sys = substr($string->filename, -8) === '.sys.ini' ? '.sys' : '';

				$language->load($string->extension . $sys, $client->path, false, false);

				$data->language->COM_SELLACIOUS_CART_SHIPMENT_STATUS_TBD_DISCLAIMER = $language->getString($string->lang_constant);
			}
		}
	}
}
