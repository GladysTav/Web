<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Event;

// no direct access
defined('_JEXEC') or die;

use Exception;
use JCacheControllerCallback;
use JFactory;
use JLoader;
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Event\EventInterface;
use Joomla\String\Normalise;

/**
 * Event helper class
 *
 * @since   2.0.0
 */
class EventHelper
{
	/**
	 * The global singleton for event observer
	 *
	 * @var    Dispatcher
	 *
	 * @since   2.0.0
	 */
	protected static $dispatcher = null;

	/**
	 * Get the global event dispatcher object
	 *
	 * @return  Dispatcher
	 *
	 * @since   2.0.0
	 */
	public static function getDispatcher()
	{
		if (static::$dispatcher === null)
		{
			static::$dispatcher = new Dispatcher;

			try
			{
				// Try to load dispatcher with observers
				static::load();
			}
			catch (Exception $e)
			{
				// Assign a fake dispatcher
			}
		}

		return static::$dispatcher;
	}

	/**
	 * Shorthand method to trigger an event
	 *
	 * @param   string  $eventName  name of the event to trigger
	 * @param   array   $arguments  An associative array to be bound to event
	 *
	 * @return  EventInterface
	 *
	 * @since   2.0.0
	 */
	public static function trigger($eventName, array $arguments)
	{
		$event = new Event($eventName);

		foreach ($arguments as $key => $value)
		{
			$event->setArgument($key, $value);
		}

		return EventHelper::getDispatcher()->triggerEvent($event);
	}

	/**
	 * Loads the event handlers for all extensions
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected static function load()
	{
		$loader = function () {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Plugins in sellacious will have different structure than [plgGroupElement], to do later
			$types = array('component', 'module');

			$query->select($db->qn(array('type', 'folder', 'element', 'client_id')))
			      ->from('#__extensions')
			      ->where('type IN (' . implode(', ', $db->q($types)) . ')')
			      ->where('enabled = 1')
			      ->where('state IN (0, 1)')
			      ->order('ordering ASC');

			// Exclude Joomla native extensions, any identical named extensions will also be skipped
			$query->where('extension_id > 9999');

			return $db->setQuery($query)->loadObjectList();
		};

		/** @var  JCacheControllerCallback $cache */
		$cache = JFactory::getCache('sellacious.events', 'callback');

		static::$dispatcher = new Dispatcher;

		$extensions = $cache->get($loader, array(), 'EventHelper::load', false);

		foreach ($extensions as $extension)
		{
			if ($extension->type === 'component')
			{
				$path      = JPATH_SELLACIOUS;
				$filename  = $path . '/components/' . $extension->element . '/observer.php';
				$className = Normalise::toCamelCase($extension->element) . 'Observer';
			}
			else
			{
				$path      = $extension->client_id == 0 ? JPATH_SITE : JPATH_SELLACIOUS;
				$filename  = $path . '/modules/' . $extension->element . '/observer.php';
				$className = Normalise::toCamelCase($extension->element) . 'Observer' . ($extension->client_id == 0 ? 'Site' : '');
			}

			if (file_exists($filename))
			{
				JLoader::register($className, $filename);

				if (class_exists($className))
				{
					// Load language for extension
					JFactory::getLanguage()->load($extension->element . '.sys', dirname($filename));
					JFactory::getLanguage()->load($extension->element . '.sys', $path);

					new $className(static::$dispatcher);
				}
			}
		}
	}
}
