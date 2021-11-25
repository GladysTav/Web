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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Cache\Reader\ProductsCacheReader;
use Sellacious\Config\ConfigHelper;
use Sellacious\Event\Observer\AbstractObserver;
use Sellacious\Hyperlocal\Settings;

require_once __DIR__ . '/helper.php';

/**
 * Class ModSellaciousHyperlocalObserverSite
 *
 * @package  Sellacious\Event\Observer
 *
 * @since    2.0.0
 */
class ModSellaciousHyperlocalObserverSite extends AbstractObserver
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
			'onFetchCacheItems'  => Priority::NORMAL,
			'onPrepareForm'      => Priority::NORMAL,
			'onPrepareFormData'  => Priority::NORMAL,
			'onProcessListQuery' => Priority::NORMAL,
		);

		return $events;
	}

	/**
	 * Add filters to the products cache records reader
	 *
	 * @param   AbstractEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onFetchCacheItems(AbstractEvent $event)
	{
		$app     = JFactory::getApplication();
		$config  = Settings::getInstance();
		$context = $event->getArgument('context');

		if (!$app->isClient('site') || !$config->isEnabled())
		{
			return;
		}

		if ($context !== 'com_sellacious.products' && strpos($context, 'mod_sellacious_products.') !== 0)
		{
			return;
		}

		$loader = $event->getArgument('loader');
		$data   = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());

		if ($config->get('location_type') == SellaciousHyperlocal::BY_LOCATION)
		{
			$this->addProductLocationFilter($loader, $config, $data);
		}
		elseif ($config->get('location_type') == SellaciousHyperlocal::BY_SHIPPABLE)
		{
			$this->addProductShippableFilter($loader, $config, $data);
		}
	}

	/**
	 * Adds hyperlocal configuration
	 *
	 * @param   AbstractEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onPrepareForm(AbstractEvent $event)
	{
		$app    = JFactory::getApplication();
		$config = Settings::getInstance();
		$form   = $event->getArgument('form');
		$data   = $event->getArgument('data');

		if (!$app->isClient('sellacious') || !$form instanceof JForm)
		{
			return;
		}

		$name = $form->getName();
		$obj  = is_array($data) ? ArrayHelper::toObject($data) : $data;

		JFormHelper::addFieldPath(__DIR__ . '/libraries/fields');

		JFactory::getLanguage()->load('mod_sellacious_hyperlocal', __DIR__);
		JFactory::getLanguage()->load('mod_sellacious_hyperlocal', JPATH_ROOT);

		if ($name == 'com_sellacious.config')
		{
			$form->loadFile(__DIR__ . '/config.xml', false);
		}

		if (!$config->isEnabled())
		{
			return;
		}

		if (($name == 'com_sellacious.user' || $name == 'com_sellacious.profile') && isset($obj->seller, $obj->seller->category_id) && $obj->seller->category_id)
		{
			$map = array(
				'address'     => 'store_address_input',
				'coordinates' => 'store_location',
				'country'     => 'loc_country',
				'state'       => 'loc_state',
				'district'    => 'loc_district',
				'city'        => 'loc_city',
				'locality'    => 'loc_locality',
				'sublocality' => 'loc_sublocality',
				'zip'         => 'loc_zip',
			);

			$form->loadFile(__DIR__ . '/forms/seller.xml', false);
			$form->setFieldAttribute('store_location', 'type', 'mapAddress', 'seller');
			$form->setFieldAttribute('store_location', 'search_box', 'true', 'seller');
			$form->setFieldAttribute('store_location', 'field_map', http_build_query($map), 'seller');
		}
		elseif ($name == 'com_sellacious.product')
		{
			$map = array(
				'address'     => 'product_address',
				'coordinates' => 'product_location',
				'country'     => 'loc_country',
				'state'       => 'loc_state',
				'district'    => 'loc_district',
				'city'        => 'loc_city',
				'locality'    => 'loc_locality',
				'sublocality' => 'loc_sublocality',
				'zip'         => 'loc_zip',
			);

			$form->setFieldAttribute('product_address', 'type', 'mapAddress', 'seller');
			$form->setFieldAttribute('product_address', 'field_map', http_build_query($map), 'seller');
		}
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   AbstractEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function onPrepareFormData(AbstractEvent $event)
	{
		$app     = JFactory::getApplication();
		$context = $event->getArgument('context');
		$data    = $event->getArgument('data');

		if (!$app->isClient('sellacious') || !is_object($data))
		{
			return;
		}

		if ($context == 'com_sellacious.config')
		{
			$config = ConfigHelper::getInstance('mod_sellacious_hyperlocal');

			if (count($config->getParams()))
			{
				$data->mod_sellacious_hyperlocal = $config->getParams();
			}
		}
	}

	/**
	 * Runs on preparation of model's list query
	 *
	 * @param   AbstractEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @see     JModelList::getListQuery()
	 *
	 * @since   2.0.0
	 */
	public function onProcessListQuery(AbstractEvent $event)
	{
		$app     = JFactory::getApplication();
		$config  = Settings::getInstance();
		$context = $event->getArgument('context');

		if (!$app->isClient('site') || !$config->isEnabled())
		{
			return;
		}

		if ($context !== 'com_sellacious.stores' && $context !== 'mod_sellacious_stores')
		{
			return;
		}

		$loader = new ProductsCacheReader;
		$data   = $app->getUserState('mod_sellacious_hyperlocal.user.location', array());
		$sql    = $event->getArgument('query');

		$loader->getQuery()->clear('select')->select('seller_uid')->group('seller_uid');

		if ($config->get('location_type') == SellaciousHyperlocal::BY_LOCATION)
		{
			$this->addStoreLocationFilter($loader, $config, $data);
		}
		elseif ($config->get('location_type') == SellaciousHyperlocal::BY_SHIPPABLE)
		{
			$this->addStoreShippableFilter($loader, $config, $data);
		}

		$items = $loader->getItems();
		$pks   = ArrayHelper::getColumn($items, 'seller_uid');

		$pks ? $sql->where('a.user_id IN (' . implode(', ', $pks) . ')') : $sql->where('0');
	}

	/**
	 * Add filters by products location
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   Settings             $config
	 * @param   array                $data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addProductLocationFilter(ProductsCacheReader $loader, Settings $config, array $data)
	{
		if ($config->get('hyperlocal_type') == SellaciousHyperlocal::BY_RADIUS)
		{
			$bMax = ArrayHelper::getValue($data, 'bounds_max', array(), 'array');
			$bMin = ArrayHelper::getValue($data, 'bounds_min', array(), 'array');

			if (count($bMax) === 4 && count($bMin) === 4)
			{
				$loader->filterInMapBoundary('product_lat', 'product_lng', $bMax['north'], $bMax['east'], $bMax['west'], $bMax['south']);
				$loader->filterNotInMapBoundary('product_lat', 'product_lng', $bMin['north'], $bMin['east'], $bMin['west'], $bMin['south']);
			}
		}
		elseif ($config->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION)
		{
			$parts = $config->getAutofillComponents();
			$query = $loader->getQuery();
			$cond  = array();

			foreach ($parts as $comp)
			{
				if ($value = ArrayHelper::getValue($data, $comp))
				{
					$cond[] = sprintf('COALESCE(%1$s, %2$s) = %3$s', $query->qn('psx_' . $comp), $query->qn('store_' . $comp), $query->q($value));
				}
			}

			if ($cond)
			{
				$query->where('(' . implode(' AND ', $cond) . ')');
			}
		}
	}

	/**
	 * Add filters by store location
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   Settings             $config
	 * @param   array                $data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addStoreLocationFilter(ProductsCacheReader $loader, Settings $config, array $data)
	{
		if ($config->get('hyperlocal_type') == SellaciousHyperlocal::BY_RADIUS)
		{
			$bMax = ArrayHelper::getValue($data, 'bounds_max', array(), 'array');
			$bMin = ArrayHelper::getValue($data, 'bounds_min', array(), 'array');

			if (count($bMax) === 4 && count($bMin) === 4)
			{
				$loader->filterInMapBoundary('store_lat', 'store_lng', $bMax['north'], $bMax['east'], $bMax['west'], $bMax['south']);
				$loader->filterNotInMapBoundary('store_lat', 'store_lng', $bMin['north'], $bMin['east'], $bMin['west'], $bMin['south']);
			}
		}
		elseif ($config->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION)
		{
			$parts = $config->getAutofillComponents();
			$query = $loader->getQuery();
			$cond  = array();

			foreach ($parts as $comp)
			{
				if ($value = ArrayHelper::getValue($data, $comp))
				{
					$cond[] = $query->qn('store_' . $comp) . ' = ' . $query->q($value);
				}
			}

			if ($cond)
			{
				$query->where('(' . implode(' AND ', $cond) . ')');
			}
		}
	}

	/**
	 * Add filters by store location
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   Settings             $config
	 * @param   array                $data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addProductShippableFilter($loader, $config, $data)
	{
		$this->addShippableFilter($loader, $config, $data);
	}

	/**
	 * Add filters by store location
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   Settings             $config
	 * @param   array                $data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addStoreShippableFilter($loader, $config, $data)
	{
		$this->addShippableFilter($loader, $config, $data);
	}

	/**
	 * Add filters by store location
	 *
	 * @param   ProductsCacheReader  $loader
	 * @param   Settings             $config
	 * @param   array                $data
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addShippableFilter($loader, $config, $data)
	{
		if ($config->get('hyperlocal_type') == SellaciousHyperlocal::BY_RADIUS)
		{
			// Todo
		}

		if (isset($data['id']) && $config->get('hyperlocal_type') == SellaciousHyperlocal::BY_REGION)
		{
			try
			{
				$helper    = SellaciousHelper::getInstance();
				$queried   = $helper->location->getAncestry($data['id'], 'A');
				$queried[] = 1;

				$loader->filterIntersectJsonArray('seller_gl_id', 'all', (array) $queried);
			}
			catch (Exception $e)
			{
				// Ignore
			}
		}
	}
}
