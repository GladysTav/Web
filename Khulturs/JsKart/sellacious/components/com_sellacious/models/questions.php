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
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Sellacious Questions.
 *
 * @since   1.6.0
 */
class SellaciousModelQuestions extends SellaciousModelList
{
	/**
	 * Constructor.
	 *
	 * @param  array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 *
	 * @since   1.6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'question', 'a.question',
				'seller_uid', 'a.seller_uid',
				'state', 'a.state',
				'product_title','p.title',
			);
		}

		parent::__construct($config);
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		if (!$this->helper->access->check('question.list'))
		{
			// Filter is not available for them
			$this->state->set('filter.seller_uid', null);
		}
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      Data
	 * @param   boolean  $loadData  Load current data
	 *
	 * @return  JForm|bool  The JForm object or false
	 *
	 * @since   1.6.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form instanceof JForm)
		{
			if (!$this->helper->access->check('question.list'))
			{
				$form->removeField('seller_uid', 'filter');
			}
		}

		return $form;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*')->from($db->qn('#__sellacious_product_questions', 'a'));

		$query->select('p.title AS product_title')
			->join('left', '#__sellacious_products AS p ON p.id = a.product_id');

		$query->select('v.title AS variant_title')
			->join('left', '#__sellacious_variants AS v ON v.id = a.variant_id');

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->q('%' . $db->escape($search, true) . '%');

				$query->where('a.question LIKE ' . $search);
			}
		}

		if ($this->helper->access->check('question.list'))
		{
			$seller_uid = $this->getState('filter.seller_uid');

			if (is_numeric($seller_uid))
			{
				$query->where('a.seller_uid = ' . (int) $seller_uid);
			}
		}
		elseif ($this->helper->access->check('question.list.own'))
		{
			$me = JFactory::getUser();

			$query->where('a.seller_uid = ' . (int) $me->id);
		}
		else
		{
			$query->where('0');
		}

		// Filter by replied state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);
		}
		elseif ($state == '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Add the list ordering clause.
		$ordering = $this->state->get('list.fullordering', 'a.state ASC');

		if (trim($ordering))
		{
			$query->order($db->escape($ordering));
		}

		return $query;
	}

	protected function processList($items)
	{
		foreach ($items as &$item)
		{
			$item->code = $this->helper->product->getCode($item->product_id, $item->variant_id, $item->seller_uid);
		}
		return $items;
	}
}
