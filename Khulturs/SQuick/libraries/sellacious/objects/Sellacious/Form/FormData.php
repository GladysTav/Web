<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Form;

// no direct access.
defined('_JEXEC') or die;

use Sellacious\Form\FormData\Item;
use stdClass;

/**
 * Class for Form Data
 *
 * @since   2.0.0
 */
class FormData
{
	/**
	 * The form data
	 *
	 * @var   array
	 *
	 * @since   2.0.0
	 */
	protected $data;

	/**
	 * The form data items
	 *
	 * @var   Item[]
	 *
	 * @since   2.0.0
	 */
	protected $items;

	/**
	 * FormData constructor.
	 *
	 * @param   array  $data  Form data
	 *
	 * @since   2.0.0
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Method to build form data items
	 *
	 * @since  2.0.0
	 */
	public function buildItems()
	{
		if ($this->data)
		{
			foreach ($this->data as $key => $item)
			{
				if (!is_string($key))
				{
					continue;
				}

				$dataItem = new Item($key, $item);
				$this->addItem($dataItem);
			}
		}
	}

	/**
	 * Method to get form data as an object list
	 *
	 * @return  stdClass[]
	 *
	 * @since   2.0.0
	 */
	public function getItemsList()
	{
		$data = array();

		if ($dataItems = $this->getItems())
		{
			foreach ($dataItems as $dataItem)
			{
				$item        = new stdClass();
				$item->name  = $dataItem->getName();
				$item->label = $dataItem->getLabel();
				$item->value = $dataItem->getValue();

				$data[] = $item;
			}
		}

		return $data;
	}

	/**
	 * @param   Item  $item
	 *
	 * @since   2.0.0
	 */
	public function addItem($item)
	{
		$this->items[] = $item;
	}

	/**
	 * @return  Item[]
	 *
	 * @since   2.0.0
	 */
	public function getItems()
	{
		return $this->items;
	}
}
