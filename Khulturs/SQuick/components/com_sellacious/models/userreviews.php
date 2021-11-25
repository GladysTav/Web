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
use Sellacious\Product;

defined('_JEXEC') or die;

/**
 * Methods supporting a list of User Reviews and ratings.
 *
 * @since   2.0.0
 */
class SellaciousModelUserReviews extends SellaciousModelList
{
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

		$me         = JFactory::getUser();
		$authorId   = $this->app->input->getInt('author_id', $me->id);
		$reviewsBy = $this->app->input->get('reviews_by', '');

		if ($reviewsBy)
		{
			$this->state->set('filter.reviews_by', $reviewsBy);
		}

		if ($authorId)
		{
			$this->state->set('filter.author_id', $authorId);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   2.0.0
	 */
	protected function getListQuery()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$authorId   = $this->getState('filter.author_id', 0);
		$reviewsBy = $this->getState('filter.reviews_by', '');

		$query->select('a.*, d.title as product_title, s.title as seller_title');
		$query->from($db->qn('#__sellacious_ratings', 'a'));

		$query->join('left', $db->qn('#__sellacious_products', 'd') . ' ON d.id = a.product_id');
		$query->join('left', $db->qn('#__sellacious_sellers', 's') . ' ON s.user_id = a.seller_uid');
		$query->join('left', $db->qn('#__sellacious_product_sellers', 'psx') . ' ON psx.seller_uid = a.seller_uid AND psx.product_id = a.product_id');

		$contexts = array();

		if (!$reviewsBy)
		{
			$contexts[] = 'product';
			$contexts[] = 'seller';
		}
		else
		{
			$contexts[] = $reviewsBy;
		}

		$query->where('a.state = 1');
		$query->where('a.type IN (' . implode(',', $db->quote($contexts)) . ')');

		// Whether the product is published
		$query->where('d.state = 1');

		// Whether the product is being sold by the seller
		$query->where('psx.state = 1');

		// Filter by author id
		if ($authorId)
		{
			$query->where('a.author_id = ' . (int) $authorId);
		}

		$query->order('a.created DESC');

		return $query;
	}

	/**
	 * Process list to add items in review
	 *
	 * @param   array  $items
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	protected function processList($items)
	{
		if (is_array($items))
		{
			foreach ($items as &$item)
			{
				$product = new Product($item->product_id, $item->variant_id, $item->seller_uid);

				$item->product       = $product;
				$item->product_image = $this->helper->product->getImage($item->product_id, $item->variant_id);
			}
		}

		return parent::processList($items);
	}
}
