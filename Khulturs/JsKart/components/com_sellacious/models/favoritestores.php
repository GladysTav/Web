<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Sellacious\Event\EventHelper;

defined('_JEXEC') or die;

/**
 * Methods supporting a list of Favortie Stores
 *
 * @since   2.0.0
 */
class SellaciousModelFavoriteStores extends SellaciousModelList
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings
	 *
	 * @see     JControllerLegacy
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'title',
				'a.title',
				'state',
				'a.state',
			);
		}
		
		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set
	 *
	 * Note. Calling getState in this method will result in recursion
	 *
	 * @param   string  $ordering   An optional ordering field
	 * @param   string  $direction  An optional direction (asc|desc)
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		
		$arguments = array(
			'context' => 'com_sellacious.favoritestores',
			'state'   => &$this->state,
		);
		
		$uid  = $this->app->input->get('user_id');
		$user = JFactory::getUser($uid);
		
		$this->state->set('favoritestores.user_id', $user->id);
		
		EventHelper::trigger('onPopulateState', $arguments);
	}
	
	/**
	 * Build an SQL query to load the list data
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$userId = $this->state->get('favoritestores.user_id');
		
		// Select the required fields from the table
		$query->select('a.*')
			->from($db->qn('#__sellacious_user_favorites', 'f'))
			->join('INNER', $db->qn('#__sellacious_sellers', 'a') . ' ON a.user_id = f.record_id')
			->where('a.state = 1');
		
		$query->where('f.context = ' . $db->q('store'));
		$query->where('f.author_id = ' . (int) $userId);
		
		$query->select(' u.name, u.username, u.email')
			->join('INNER', $db->qn('#__users', 'u') . ' ON a.user_id = u.id')
			->where('u.block = 0')
			->order('a.created DESC');
		
		$arguments = array(
			'context' => 'com_sellacious.favoritestores',
			'query'   => $query,
			'state'   => $this->getState(),
		);
		
		EventHelper::trigger('onProcessListQuery', $arguments);
		
		return $query;
	}
	
	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			$show = $this->helper->config->get('show_store_product_count', 1);
			
			foreach ($items as $item)
			{
				$item->profile       = $this->helper->profile->getItem(array('user_id' => $item->user_id));
				$item->rating        = $this->helper->rating->getSellerRating($item->user_id);
				$item->product_count = $show ? $this->helper->seller->getSellerProductCount($item->user_id) : null;
			}
		}
		
		return $items;
	}
}