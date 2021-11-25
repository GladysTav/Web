<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * View class for a list of shoprules.
 */
class SellaciousViewShoprules extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'shoprule';

	/** @var  string */
	protected $view_item = 'shoprule';

	/** @var  string */
	protected $view_list = 'shoprules';

	/** @var  bool */
	protected $is_nested = true;

	/** @var  array */
	protected $types = array();

	/** @var  array */
	protected $method_names = array();

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareDisplay()
	{
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		$this->types = $this->helper->shopRule->getTypes();

		$handlers           = $this->helper->shopRule->getHandlers();
		$this->method_names = array('*' => JText::_('COM_SELLACIOUS_SHOPRULE_METHOD_BASIC'));

		foreach ($handlers as $key => $handler)
		{
			$this->method_names[$key] = JText::_($handler->title);
		}

		parent::prepareDisplay();
	}
}
