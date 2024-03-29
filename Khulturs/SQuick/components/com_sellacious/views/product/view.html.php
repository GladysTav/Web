<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Access\AccessHelper;
use Sellacious\UI\VariantSwitcher\Field as SwitcherField;
use Sellacious\UI\VariantSwitcher\Switcher as VariantSwitcher;
use Sellacious\UI\VariantSwitcher\Variant as SwitcherVariant;

/**
 * View to edit
 *
 * @since   1.0.0
 */
class SellaciousViewProduct extends SellaciousView
{
	/**
	 * @var  JDocumentHTML
	 *
	 * @since   1.0.0
	 */
	public $document;

	/**
	 * @var  JObject
	 *
	 * @since   1.0.0
	 */
	protected $state;

	/**
	 * @var  Registry
	 *
	 * @since   1.0.0
	 */
	protected $item;

	/**
	 * @var  JForm
	 *
	 * @since   1.0.0
	 */
	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.2.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		if ($this->_layout == 'default')
		{
			/**
			 * If detail view is disabled, we should not continue.
			 * However administrators and seller (for own items) can be permitted for the obvious reason.
			 */
			$me          = JFactory::getUser();
			$show_detail = $this->helper->config->get('product_detail_page');
			$is_admin    = AccessHelper::allow('app.manage');
			$is_seller   = $this->state->get('product.seller_uid') == $me->id;

			if (!$show_detail && !$is_admin && !$is_seller)
			{
				JLog::add(JText::_('COM_SELLACIOUS_PAGE_NOT_FOUND'), JLog::WARNING, 'jerror');

				$redirect = $this->helper->config->get('redirect', 'index.php');

				$this->app->redirect(JRoute::_($redirect, false));

				// This should not be needed.
				return false;
			}

			try
			{
				$item       = $this->get('Item');
				$this->item = new Registry($item);
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

				return false;
			}
		}
		elseif ($this->_layout == 'query')
		{
			if ($this->app->input->get('sent'))
			{
				$tpl = 'sent';
			}
			else
			{
				try
				{
					$item       = $this->get('Item');
					$this->item = new Registry($item);

					$this->form = $this->get('QueryForm');

					if (!$this->form instanceof JForm)
					{
						throw new Exception(JText::_('COM_SELLACIOUS_PRODUCT_QUERY_FORM_LOAD_FAILED'), JLog::WARNING, 'jerror');
					}
				}
				catch (Exception $e)
				{
					JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}
		elseif ($this->_layout == 'coq')
		{
			try
			{
				$item       = $this->get('Item');
				$this->item = new Registry($item);
				$this->form = $this->get('CheckoutQuestionForm');
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

				return false;
			}
		}
		else
		{
			try
			{
				$item       = $this->get('Item');
				$this->item = new Registry($item);
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

				return false;
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		if ($this->item && ($title = $this->item->get('title')))
		{
			if (empty($title))
			{
				$title = $this->app->get('sitename');
			}
			elseif ($this->app->get('sitename_pagetitles', 0) == 1)
			{
				$title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
			}
			elseif ($this->app->get('sitename_pagetitles', 0) == 2)
			{
				$title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
			}

			$this->document->setTitle($title);

			$this->document->setMetaData('keywords', $this->item->get('metakey'));
			$this->document->setMetaData('description', $this->item->get('metadesc', $this->item->get('introtext')));

			$this->setPathway();
		}

		return parent::display($tpl);
	}

	/**
	 * Method to add pathway for the breadcrumbs to the view
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setPathway()
	{
		$productId = $this->state->get('product.id');

		if ($productId)
		{
			try
			{
				$db  = JFactory::getDbo();
				$sql = $db->getQuery(true);

				$sql->select('a.id')->from($db->qn('#__sellacious_categories', 'a'))->order('a.lft ASC');
				$sql->join('inner', $db->qn('#__sellacious_product_categories', 'pc') . ' ON pc.category_id = a.id')
					->where('pc.product_id = ' . (int) $productId);
				$catid = $db->setQuery($sql)->loadResult();
			}
			catch (Exception $e)
			{
				return;
			}

			if ($catid)
			{
				$pks    = $this->helper->category->getParents($catid, true);
				$filter = array('id' => $pks, 'list.select' => 'a.id, a.title', 'list.where' => 'a.level > 0');
				$cats   = $this->helper->category->loadObjectList($filter);
				$crumbs = array();

				foreach ($cats as $cat)
				{
					$crumb = new stdClass;
					$link  = JRoute::_('index.php?option=com_sellacious&view=categories&category_id=' . (int) $cat->id);

					$crumb->name = $cat->title;
					$crumb->link = $link;

					$crumbs[] = $crumb;
				}

				$crumb = new stdClass;

				$crumb->name = $this->item->get('title') . ' ' . ($this->item->get('variant_title') ?: '');
				$crumb->link = null;

				$crumbs[] = $crumb;

				$this->app->getPathway()->setPathway($crumbs);
			}
		}
	}

	/**
	 * Get choices of the variants options to be selected by the customer
	 *
	 * @return  stdClass[]
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 *
	 * @deprecated   Use \Sellacious\UI\VariantSwitcher\Switcher with layout: default_variant_switcher
	 */
	public function getVariantChoices()
	{
		if (!$this->helper->config->get('multi_variant', 0))
		{
			return array();
		}

		$specLists   = array();
		$product_id  = $this->item->get('id');
		$variant_id  = $this->item->get('variant_id');
		$variant_ids = $this->helper->variant->loadColumn(array('list.select' => 'a.id', 'product_id' => $product_id));

		// Preload product fields for the getSpecifications call to save repetitive evaluating inside it.
		$vFields   = $this->helper->product->getFields($product_id, array('variant'));
		$specs     = $this->helper->variant->getProductSpecifications($product_id, $vFields, false);
		$specsFlat = array();

		foreach ($specs as $specKey => $specValue)
		{
			if (is_array($specValue))
			{
				// Use first value of a multivalued spec, is this ok?
				$specsFlat[$specKey] = reset($specValue);
			}
			else
			{
				$specsFlat[$specKey] = $specValue;
			}
		}

		$specLists[0] = $specsFlat;

		foreach ($variant_ids as $vid)
		{
			$specs     = $this->helper->variant->getSpecifications($vid, $vFields, false);
			$specsFlat = array();

			foreach ($specs as $specKey => $specValue)
			{
				if (is_array($specValue))
				{
					// Use first value of a multivalued spec, is this ok? @20190415@ NO!
					// $specsFlat[$specKey] = reset($specValue);
				}
				else
				{
					$specsFlat[$specKey] = $specValue;
				}
			}

			$specLists[$vid] = $specsFlat;
		}

		$items = array();
		$vfTables = array('products' => $product_id, 'variants' => $variant_ids);

		foreach ($vFields as $field)
		{
			$item = new stdClass;

			$item->id      = $field->id;
			$item->type    = $field->type;
			$item->group   = $field->group;
			$item->title   = $field->title;
			$item->options = $this->helper->field->getFilterChoices($field, $vfTables);

			// Skip this variant field completely if there are no available choices
			if (count($item->options))
			{
				$values = array();

				foreach ($specLists as $vid => $specFlat)
				{
					$value = ArrayHelper::getValue($specFlat, $field->id);

					if ($value)
					{
						$values[$vid] = $value;
					}
				}

				$valueC = ArrayHelper::getValue($values, (int) $variant_id);

				$item->choices = array();

				foreach ($item->options as $option)
				{
					$choice = new stdClass;

					$choice->value   = $option;
					$choice->active  = $option == $valueC;
					$choice->enabled = in_array($option, $values);

					$item->choices[] = $choice;
				}

				$item->available   = array_unique($values);
				$item->unavailable = array_unique(array_diff($item->options, $item->available));
				$item->selected    = $valueC;

				$items[$field->id] = $item;
			}
		}

		return $items;
	}

	/**
	 * Get choices of the variants options to be selected by the customer
	 *
	 * @return  VariantSwitcher
	 *
	 * @since   1.7.0
	 */
	public function getVariantSwitcher()
	{
		if (!$this->helper->config->get('multi_variant', 0))
		{
			return null;
		}

		JLoader::registerNamespace('Sellacious\UI\VariantSwitcher', JPATH_SITE . '/components/com_sellacious/helpers/Switcher', false, false, 'psr4');

		$productId = $this->item->get('id');

		try
		{
			$fields = $this->helper->product->getFields($productId, array('variant'));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return null;
		}

		$switcher = new VariantSwitcher;

		// Add fields to the switcher
		foreach ($fields as $field)
		{
			$swField = new SwitcherField($field->id, $field->title, $field->type);
			$swField->setParams($field->params);
			$switcher->addField($swField);
		}

		// The active variant on this page
		$vid    = $this->item->get('variant_id');
		$vCode  = $this->item->get('code');
		$vStock = $this->item->get('stock_capacity');
		$values = $this->getVariantSpecification($productId, $vid, $fields);

		$swVariant = new SwitcherVariant($vid, $vCode, $vStock, $values);

		$switcher->addVariant($swVariant, true);

		// Other available variants
		foreach ($this->item->get('variants') as $variant)
		{
			$vid    = $variant->variant_id;
			$vCode  = $variant->code;
			$vStock = $variant->seller->stock_capacity;
			$values = $this->getVariantSpecification($productId, $vid, $fields);

			$swVariant = new SwitcherVariant($vid, $vCode, $vStock, $values);

			$switcher->addVariant($swVariant);
		}

		return $switcher;
	}

	/**
	 * Get the applicable review form
	 *
	 * @return  JForm
	 *
	 * @since   1.0.0
	 */
	public function getReviewForm()
	{
		static $form = null;

		if (!isset($form))
		{
			$product_id = $this->item->get('id');
			$variant_id = $this->item->get('variant_id');
			$seller_uid = $this->item->get('seller_uid');

			$form = $this->helper->rating->getForm($product_id, $variant_id, $seller_uid);

			if (!($form instanceof JForm) || count($fieldset = $form->getFieldset()) == 0)
			{
				$form = false;
			}
		}

		return $form;
	}

	/**
	 * Get the Question form
	 *
	 * @return  JForm
	 *
	 * @since   1.6.0
	 */
	public function getQuestionForm()
	{
		static $form = null;

		if (!isset($form))
		{
			$product_id = $this->item->get('id');
			$variant_id = $this->item->get('variant_id');
			$seller_uid = $this->item->get('seller_uid');

			$form = $this->helper->product->getQuestionForm($product_id, $variant_id, $seller_uid);

			if (!($form instanceof JForm) || count($fieldset = $form->getFieldset()) == 0)
			{
				$form = false;
			}
		}

		return $form;
	}

	/**
	 * Get the applicable review form
	 *
	 * @return  stdClass
	 *
	 * @since   1.4.0
	 */
	public function getSampleMedia()
	{
		$product_id = (int) $this->item->get('id');
		$variant_id = (int) $this->item->get('variant_id');
		$seller_uid = (int) $this->item->get('seller_uid');

		$filter = array(
			'list.select'=> 'a.id, a.table_name, a.record_id, a.context, a.path, a.original_name, a.doc_type, a.doc_reference',
			'list.join'  => array(
				array('inner', '#__sellacious_eproduct_media AS epm ON epm.id = a.record_id'),
			),
			'list.where' => array(

				'epm.product_id = ' . $product_id,
				'epm.variant_id = ' . $variant_id,
				'epm.seller_uid = ' . $seller_uid,
			),
			'table_name' => 'eproduct_media',
			'context'    => 'sample',
			'state'      => 1,
		);

		$sampledata  = $this->helper->media->loadObject($filter);

		return $sampledata;
	}

	/**
	 * Get a list of questions for current product
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getQuestions()
	{
		$product_id = $this->item->get('id');
		$variant_id = $this->item->get('variant_id');
		$seller_uid = $this->item->get('seller_uid');

		$questions = $this->helper->product->getQuestions($product_id, $variant_id, $seller_uid);

		return $questions;
	}

	/**
	 * Get a list of reviews/ratings for current product
	 *
	 * @param   string  $limit  Number of maximum records that should be returned
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getReviews($limit = null)
	{
		$multiVariant    = $this->helper->config->get('multi_variant', 0);
		$variantSeparate = $multiVariant == 2;

		$filters = array(
			'type'       => 'product',
			'product_id' => (int) $this->item->get('id'),
			'state'      => 1,
			'list.limit' => (int) $limit,
		);

		if ($variantSeparate)
		{
			$filters['variant_id'] = (int) $this->item->get('variant_id');
		}

		$list = $this->helper->rating->loadObjectList($filters);

		return $list;
	}

	/**
	 * Get consolidated stats of ratings for current product
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getReviewStats()
	{
		$list            = array();
		$multiVariant    = $this->helper->config->get('multi_variant', 0);
		$variantSeparate = $multiVariant == 2;

		$filters = array(
			'list.select' => array('COUNT(1) AS count'),
			'list.where'  => array('a.rating > 0'),
			'type'        => 'product',
			'product_id'  => (int) $this->item->get('id'),
			'state'       => 1,
		);

		if ($variantSeparate)
		{
			$filters['variant_id'] = (int) $this->item->get('variant_id');
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

	/**
	 * Get the variant specification for the given variant
	 *
	 * @param   int    $productId
	 * @param   int    $variantId
	 * @param   array  $fields
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	protected function getVariantSpecification($productId, $variantId, array $fields)
	{
		try
		{
			if ($variantId)
			{
				$values = $this->helper->variant->getSpecifications($variantId, $fields, false, null, false);
			}
			else
			{
				$values = $this->helper->variant->getProductSpecifications($productId, $fields, false, null, false);
			}

			return (array) $values;
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return array();
		}
	}
}
