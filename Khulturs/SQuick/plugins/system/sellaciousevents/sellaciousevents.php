<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Sellacious\Event\EventHelper;

jimport('sellacious.loader');

/**
 * Sellacious events forward plugin
 *
 * @since  2.0.0
 */
class PlgSystemSellaciousEvents extends JPlugin
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    bool
	 *
	 * @since  2.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Forward onAfterInitialise events
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onAfterInitialise()
	{
		if (class_exists('Sellacious\Event\EventHelper'))
		{
			EventHelper::trigger('onAfterInitialise', array());
		}
	}


	/**
	 * Forward onAfterRoute events
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onAfterRoute()
	{
		if (class_exists('Sellacious\Event\EventHelper'))
		{
			EventHelper::trigger('onAfterRoute', array());
		}
	}

	/**
	 * Forward prepareForm events
	 *
	 * @param   JForm  $form  The form to be altered
	 * @param   array  $data  The associated data for the form
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (class_exists('Sellacious\Event\EventHelper'))
		{
			EventHelper::trigger('onPrepareForm', array('form' => &$form, 'data' => &$data));
		}
	}

	/**
	 * Forwards prepareFormData events
	 *
	 * @param   string  $context  The context for the data
	 * @param   mixed   $data     An object containing the data for the form.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (class_exists('Sellacious\Event\EventHelper'))
		{
			EventHelper::trigger('onPrepareFormData', array('context' => $context, 'data' => &$data));
		}
	}

	/**
	 * Forwards contentAfterSave events
	 *
	 * @param   string  $context  The calling context
	 * @param   object  $table    A JTable object
	 * @param   bool    $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		if (class_exists('Sellacious\Event\EventHelper'))
		{
			EventHelper::trigger('onContentAfterSave', array('context' => $context, 'table' => &$table, 'isNew' => $isNew));
		}
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if (class_exists('Sellacious\Event\EventHelper') && $context === 'com_sellacious.emailtemplate')
		{
			$event = EventHelper::trigger('onFetchNotificationContext', array('context' => 'com_sellacious.notification', 'contexts' => $contexts));

			$contexts = $event->getArgument('contexts');
		}
	}
}
