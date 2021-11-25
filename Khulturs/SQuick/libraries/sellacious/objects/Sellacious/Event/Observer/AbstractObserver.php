<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Event\Observer;

// no direct access
defined('_JEXEC') or die;

use JFactory;
use Joomla\Event\Dispatcher;

/**
 * Event observer class interface
 *
 * @since   2.0.0
 */
abstract class AbstractObserver
{
	/**
	 * A persistent cache of the loaded plugins
	 *
	 * @var   Dispatcher
	 *
	 * @since   2.0.0
	 */
	protected $dispatcher = null;

	/**
	 * AbstractObserver constructor.
	 *
	 * @param   Dispatcher  $dispatcher
	 *
	 * @since   2.0.0
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$dispatcher->addListener($this, $this->getEventsMap());

		$this->dispatcher = $dispatcher;
	}

	/**
	 * Get global application instance
	 *
	 * @return  \JApplicationCms
	 *
	 * @since   2.0.0
	 */
	public function getApplication()
	{
		try
		{
			return JFactory::getApplication();
		}
		catch (\Exception $e)
		{
			return null;
		}
	}

	/**
	 * Method to return events to be observed by this observer
	 *
	 * @return  array  An array of events with method name as key and priority as value
	 *
	 * @since   2.0.0
	 */
	abstract protected function getEventsMap();
}
