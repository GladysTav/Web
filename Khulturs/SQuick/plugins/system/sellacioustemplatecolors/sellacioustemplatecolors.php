<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Saurabh Sabharwal <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Sellacious\Config\ConfigHelper;
use Sellacious\Template\Colors;

// Include dependencies
jimport('sellacious.loader');

JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries');

class plgSystemSellaciousTemplateColors extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since   2.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds color selector fields to global configuration
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() !== 'com_sellacious.config')
		{
			return true;
		}

		JForm::addFieldPath(__DIR__ . '/fields');

		$form->loadFile(__DIR__ . '/forms/colors.xml');
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepareData($context, $data)
	{
		$helper = SellaciousHelper::getInstance();

		if (!$helper->access->isSubscribed() || $context !== 'com_sellacious.config' || !is_object($data))
		{
			return true;
		}

		$config = ConfigHelper::getInstance('plg_system_sellacioustemplatecolors');

		$data->plg_system_sellacioustemplatecolors = $config->getParams();
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string  $context  The calling context
	 * @param   mixed   $data     Saved data
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @since   2.0.0
	 */
	public function onContentAfterSave($context, $data, $isNew)
	{
		$helper = SellaciousHelper::getInstance();

		if (!$helper->access->isSubscribed() || $context !== 'com_sellacious.config')
		{
			return;
		}

		$config = ConfigHelper::getInstance('plg_system_sellacioustemplatecolors');

		$colors = new Colors($config->get('colors'));
		$css    = $colors->getCss();

		file_put_contents(JPATH_SITE . '/media/sellacious/css/ctech-colors.css', $css);
	}
}
