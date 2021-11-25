<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\Event\AbstractEvent;
use Joomla\Event\Priority;
use Sellacious\Event\Observer\AbstractObserver;
use Sellacious\Form\CheckoutQuestionsFormHelper;
use Sellacious\Form\Handler\CoqHandler;

/**
 * Sellacious Checkout questions Observer
 *
 * @since  2.0.0
 */
class ComSellaciousCoqObserver extends AbstractObserver
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
			'onAfterInitialise' => Priority::NORMAL,
			'onPrepareForm'     => Priority::NORMAL,
		);

		return $events;
	}

	/**
	 * Method to register and load all necessary library classes
	 *
	 * @param   AbstractEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onAfterInitialise(AbstractEvent $event)
	{
		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries/src', false, false, 'psr4');

		CheckoutQuestionsFormHelper::addHandler('checkout_questions', CoqHandler::class);
	}
}
