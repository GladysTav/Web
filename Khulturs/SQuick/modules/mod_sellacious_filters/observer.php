<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

use Joomla\Event\AbstractEvent;
use Joomla\Event\Priority;
use Sellacious\Event\Observer\AbstractObserver;

require_once __DIR__ . '/module.php';

/**
 * Class ModSellaciousFiltersObserver
 *
 * @package  Sellacious\Event\Observer
 *
 * @since    2.0.0
 */
class ModSellaciousFiltersObserverSite extends AbstractObserver
{
	/**
	 * Method to return events to be observed by this observer
	 *
	 * @return  array  An array of events with method name as key and priority as value
	 *
	 * @since   2.0.0
	 */
	protected function getEventsMap()
	{
		$events = array(
			'onPopulateState'   => Priority::NORMAL,
			'onFetchCacheItems' => Priority::NORMAL,
		);

		return $events;
	}

	public function onPopulateState(AbstractEvent $event)
	{
		if ($event->getArgument('context') !== 'com_sellacious.products')
		{
			return;
		}

		try
		{
			$state  = $event->getArgument('state');
			$module = ModSellaciousFilters::getInstance();

			$module->populateState($state);
		}
		catch (Exception $e)
		{
			// Ignore for now
		}
	}

	public function onFetchCacheItems(AbstractEvent $event)
	{
		if ($event->getArgument('context') !== 'com_sellacious.products')
		{
			return;
		}

		try
		{
			$loader = $event->getArgument('loader');
			$state  = $event->getArgument('state');

			$module = ModSellaciousFilters::getInstance();

			$module->setQueryFilter($loader, $state);
		}
		catch (Exception $e)
		{
			// Ignore for now
		}
	}
}
