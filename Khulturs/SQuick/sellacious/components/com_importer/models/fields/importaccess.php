<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;

JFormHelper::loadFieldClass('Radio');

/**
 * Import Access radio field
 *
 * @since    2.0.0
 */
class JFormFieldImportAccess extends JFormFieldRadio
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since  2.0.0
	 */
	protected $type = 'ImportAccess';

	/**
	 * @var  SellaciousHelper
	 *
	 * @since  2.0.0
	 */
	protected $helper;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   2.0.0
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()
	{
		$options   = array();
		$types     = array('create', 'update');
		$type      = $this->element['access_type'] ?: 'create';
		$context   = $this->element['context'] ?: 'product';
		$access    = $type == 'update' ? 'edit' : $type;
		$typeTitle = strtoupper($type);

		if (in_array($type, $types))
		{
			$options[] = JHtml::_('select.option', '', 'COM_IMPORTER_FIELD_IMPORTACCESS_OPTION_' . $typeTitle . '_NONE');

			if ($context == 'product')
			{
				$allAccess = $this->helper->access->check('product.create') && $this->helper->product->canEdit(null, 'all');
				$ownAccess = $this->helper->access->check('product.create') && $this->helper->product->canEdit(null, 'own');
			}
			elseif ($context == 'variant')
			{
				$allAccess = $this->helper->access->checkAll(array('create', 'edit'), 'variant.');
				$ownAccess = $this->helper->access->checkAll(array('create', 'edit.own'), 'variant.');
			}
			else
			{
				$allAccess = $this->helper->access->check($context . '.' . $access);
				$ownAccess = $this->helper->access->check($context . '.' . $access . '.own');
			}

			if ($allAccess)
			{
				$options[] = JHtml::_('select.option', 'all', 'COM_IMPORTER_FIELD_IMPORTACCESS_OPTION_' . $typeTitle . '_ALL');
			}

			if ($ownAccess)
			{
				$options[] = JHtml::_('select.option', 'own', 'COM_IMPORTER_FIELD_IMPORTACCESS_OPTION_' . $typeTitle . '_OWN');
			}
		}

		return $options;
	}
}
