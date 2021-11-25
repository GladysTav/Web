<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * View class for a list of product variants.
 */
class SellaciousViewVariants extends SellaciousViewList
{
	/**
	 * @var  array
	 *
	 * @since   1.7.0
	 */
	protected $headings_display;

	/**
	 * Display the view
	 *
	 * @param   string $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		$this->headings_display = array();

		if (empty($this->state))
		{
			$this->state = $this->get('State');
		}

		if (empty($this->items))
		{
			$this->items = $this->get('Items');

			foreach ($this->items as $item)
			{
				$editFields = $this->helper->config->get('product_fields');
				$editFields = new Registry($editFields);
				$editCols   = $editFields->extract($item->type) ?: new Registry;

				if ($editCols->get('over_stock'))
				{
					$this->headings_display['over_stock'][] = $item->id;
				}
			}
		}

		$this->pagination    = false;
		$this->filterForm    = false;
		$this->activeFilters = false;

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	public function addToolbar()
	{
		if (!$this->helper->access->isSubscribed())
		{
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_PREMIUM_FEATURE_NOTICE_INVENTORY_MANAGER'), 'premium');
		}
		elseif ($this->helper->access->checkAny(array('pricing', 'seller', 'pricing.own', 'seller.own'), 'product.edit.'))
		{
			JToolBarHelper::apply('variants.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('variants.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel('variants.cancel', 'JTOOLBAR_CANCEL');
	}
}
