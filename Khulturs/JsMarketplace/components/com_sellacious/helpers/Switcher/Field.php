<?php
/**
 * @version     1.7.4
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2019 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\UI\VariantSwitcher;

// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

class Field
{
	/**
	 * @var   int
	 *
	 * @since   1.7.0
	 */
	public $id;

	/**
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	public $title;

	/**
	 * @var   string
	 *
	 * @since   1.7.0
	 */
	public $type;

	/**
	 * @var   FieldOption[]
	 *
	 * @since   1.7.0
	 */
	public $options = array();

	/**
	 * Constructor
	 *
	 * @param   int     $id
	 * @param   string  $title
	 * @param   string  $type
	 *
	 * @throws  \Exception
	 *
	 * @since   1.7.0
	 */
	public function __construct($id, $title, $type)
	{
		$language = \JFactory::getLanguage()->getTag();
		$helper   = \SellaciousHelper::getInstance();
		$helper->translation->translateValue($id, 'sellacious_fields', 'title', $title, $language);

		$this->id    = $id;
		$this->title = $title;
		$this->type  = $type;
	}

	/**
	 * Add an option to the field
	 *
	 * @param   FieldOption  $option
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function addOption(FieldOption $option)
	{
		$this->options[] = $option;
	}

	/**
	 * Get the option for the field, optionally create one if not exists
	 *
	 * @param   mixed  $value
	 * @param   bool   $new
	 *
	 * @return  FieldOption
	 *
	 * @since   1.7.0
	 */
	public function getOption($value, $new = false)
	{
		foreach ($this->options as $option)
		{
			if ($option->value == $value)
			{
				return $option;
			}
		}

		if ($new)
		{
			$option = new FieldOption($value);

			$this->addOption($option);

			return $option;
		}

		return null;
	}

	/**
	 * Get the options for the field, optionally filtering for only visible ones
	 *
	 * @param   bool  $visibleOnly
	 *
	 * @return  FieldOption[]
	 *
	 * @since   1.7.0
	 */
	public function getOptions($visibleOnly = false)
	{
		$options = array();

		foreach ($this->options as $option)
		{
			if (!$visibleOnly || count($option->variantsAvailable))
			{
				$options[] = $option;
			}
		}

		return ArrayHelper::sortObjects($options, 'value');
	}

	/**
	 * Check whether this field as any visible option
	 *
	 * @return  bool
	 *
	 * @since   1.7.0
	 */
	public function isVisible()
	{
		foreach ($this->options as $option)
		{
			if (count($option->variantsAvailable) > 0)
			{
				return true;
			}
		}

		return false;
	}
}
