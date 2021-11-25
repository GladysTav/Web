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
defined('_JEXEC') or die;

/**
 * Methods supporting a list of Shoprules
 *
 * @since   2.0.0
 */
class SellaciousModelShoprules extends SellaciousModelList
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
	 * @since   2.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$type = $this->app->input->getString('type', 'discount');

		if ($type)
		{
			$this->state->set('rule.type', $type);
		}
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
		$db    = $this->getDbo();
		$now   = JFactory::getDate();
		$query = $db->getQuery(true);

		$query->select('a.*')
			->from($db->qn('#__sellacious_shoprules', 'a'))
			->where('a.state = 1');

		// Exclude 'Shipping' calculation method
		$query->where('a.sum_method != 3');

		// Only 'Basic' rules
		$query->where('a.method_name = ' . $db->q('*'));

		// Type of shoprule
		$type = $this->state->get('rule.type', 'discount');

		if ($type)
		{
			$query->where('a.type = ' . $db->q($type));
		}

		// Seller filter
		$sellerUid = $this->state->get('filter.seller_uid');

		if ($sellerUid != '')
		{
			$query->where('a.seller_uid = ' . (int) $sellerUid);
		}

		// Expired rules to be put at last
		$query->order('(a.publish_down != ' . $db->q($db->getNullDate()) . ' AND a.publish_down < ' . $db->q($now->toSql()) . ') ASC');

		return $query;
	}

	/**
	 * Pre-process loaded list before returning if needed
	 *
	 * @param   stdClass[]  $items  The items loaded from the database using the list query
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function processList($items)
	{
		$g_currency = $this->helper->currency->getGlobal('code_3');
		$c_currency = $this->helper->currency->current('code_3');

		foreach ($items as $item)
		{
			$this->helper->translation->translateRecord($item, 'sellacious_shoprule');

			if (!strpos($item->amount, '%'))
			{
				$item->amount = $this->helper->currency->display($item->amount, $g_currency, $c_currency, true);
			}

			if ($item->sum_method == 2)
			{
				if ($item->apply_on_all_products)
				{
					$item->rule_applied = JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_APPLIED_ON_ALL_PRODUCTS');
				}
				else
				{
					$item->rule_applied = JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_APPLIED_ON_SELECTED_PRODUCTS');
				}
			}
			else
			{
				$item->rule_applied = JText::_('COM_SELLACIOUS_SHOPRULE_ITEM_APPLIED_ON_CART');
			}
		}

		return parent::processList($items);
	}
}
