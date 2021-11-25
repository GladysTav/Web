<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View to edit
 *
 * @property int counter
 */
class SellaciousViewProduct extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'product';

	/** @var  string */
	protected $view_item = 'product';

	/** @var  string */
	protected $view_list = 'products';

	/** @var  array */
	protected $variants;

	/**
	 * @var  bool
	 *
	 * @since   2.0.0
	 */
	public $allow_duplicates = false;

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		try
		{
			$this->state = $this->get('State');
			$this->form  = $this->get('Form');

			if (!$this->form) throw new Exception(implode('<br>', $this->get('Errors')));

			$item           = $this->form->getData();
			$this->item     = $item instanceOf Registry ? $item : new Registry;
			$this->variants = $this->helper->product->getVariants($this->item->get('id'), true, false, $this->item->get('language'));

			$this->allow_duplicates = (bool) $this->helper->config->get('allow_duplicate_products');
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		$allowEdit   = $this->helper->product->canEdit();
		$allowCreate = $this->helper->access->check($this->action_prefix . '.create') && $allowEdit;

		if ($isNew ? $allowCreate : $allowEdit)
		{
			JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
		}

		if ($allowCreate)
		{
			JToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		JToolBarHelper::cancel('product.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function setPageTitle()
	{
		$isNew   = ($this->item->get('id') == 0);
		$layout  = new JLayoutFile('sellacious.toolbar.title_extended');
		$title   = JText::_(strtoupper($this->getOption() . '_TITLE_' . $this->getName()));
		$product = $this->helper->product->getItem($this->item->get('id'));
		$title   = $isNew ? $title : $title . ': ' . $product->title;
		$args    = array('title' => $title, 'icon' => 'file', 'sub_title' => '');

		$uniqueField      = $this->state->get('product.unique_field');
		$uniqueFieldTitle = $this->state->get('product.unique_field_title');
		$uniqueFieldVal   = $this->state->get('product.unique_field_value');

		if (!$this->allow_duplicates && $uniqueField && $uniqueFieldVal)
		{
			$args['sub_title'] = JText::_($uniqueFieldTitle) . ': ' . $uniqueFieldVal;
		}

		$html = $layout->render($args);

		try
		{
			$app = JFactory::getApplication();
		}
		catch (Exception $e)
		{
		}

		$doc = JFactory::getDocument();

		$app->JComponentTitle = $html;
		$doc->setTitle($app->get('sitename') . ' - ' . strip_tags($title));
	}

	/**
	 * Check whether the field is unique
	 *
	 * @param   $fieldName
	 * @param   $fieldGroup
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	public function isFieldUnique($fieldName, $fieldGroup)
	{
		$uniqueField = $this->state->get('product.unique_field');

		if (!$this->allow_duplicates && $uniqueField)
		{
			if (is_numeric($uniqueField) && $fieldGroup && $fieldName == $uniqueField)
			{
				return true;
			}
			else
			{
				$uFieldParts = explode('.', $uniqueField);

				if ((isset($uFieldParts[1]) && $fieldGroup == $uFieldParts[0] && $fieldName == $uFieldParts[1]) || ($fieldName == $uFieldParts[0]))
				{
					return true;
				}
			}
		}

		return false;
	}
}
