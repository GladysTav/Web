<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of reviews.
 *
 * @since  1.6.0
 */
class SellaciousViewReviews extends SellaciousView
{
	/**
	 * @var  stdClass[]
	 */
	protected $items;

	/** @var  JPagination */
	protected $pagination;

	/** @var  JObject */
	protected $state;

	/** @var  stdClass */
	protected $seller;

	/** @var  stdClass[] */
	protected $seller_reviews;

	protected $product_id;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Sub-layout to load
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		// Preserve state info
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->product_id = $this->app->input->getInt('product_id', 0);

		$storeId = $this->state->get('filter.seller_uid', 0);

		$profile = $this->helper->profile->getItem(array('user_id' => $storeId));
		$seller  = $this->helper->seller->getItem(array('user_id' => $storeId));
		$rating  = $this->helper->rating->getSellerRating($storeId);

		$product_count = $this->helper->seller->getSellerProductCount($storeId);

		$this->seller          = $seller;
		$this->seller->profile = $profile;
		$this->seller->rating  = $rating;

		$this->seller->product_count = $product_count;

		$this->seller_reviews = $this->get('SellerReviews');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::ERROR, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}


	/**
	 * Get a list of reviews/ratings for current product
	 *
	 * @return  stdClass[]
	 */
	public function getReviews($limit = null)
	{
		$filters = array(
			'type'       => 'product',
			'product_id' => (int) $this->product_id,
			'state'      => 1,
			'list.where' => "a.comment != ''",
			'list.limit' => (int) $limit,
		);
		$list    = $this->helper->rating->loadObjectList($filters);

		return $list;
	}

	/**
	 * Get consolidated stats of ratings for current product
	 *
	 * @return  stdClass[]
	 */
	public function getReviewStats()
	{
		$list      = array();
		$variantId = $this->state->get('filter.variant_id', 0);

		$multiVariant    = $this->helper->config->get('multi_variant', 0);
		$variantSeparate = $multiVariant == 2;

		$filters = array(
			'list.select' => array('COUNT(1) AS count'),
			'list.where'  => array('a.rating > 0'),
			'type'        => 'product',
			'product_id'  => (int) $this->product_id,
			'state'       => 1,
		);

		if ($variantSeparate)
		{
			$filters['variant_id'] = (int) $variantId;
		}

		$total = (int) $this->helper->rating->loadResult($filters);

		if ($total > 0)
		{
			$filters['list.select'] = array('a.rating', 'COUNT(1) AS count', "$total AS total");
			$filters['list.group']  = 'a.rating';
			$filters['list.limit']  = '10';

			$list = $this->helper->rating->loadObjectList($filters, 'rating');
		}

		return $list;
	}

}
