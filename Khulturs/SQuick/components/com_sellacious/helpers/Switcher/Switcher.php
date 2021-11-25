<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\UI\VariantSwitcher;

// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use SellaciousHelper;

class Switcher
{
	/**
	 * The list of bound variants
	 *
	 * @var   Variant[]
	 *
	 * @since  1.7.0
	 */
	public $variants = array();

	/**
	 * The list of bound fields
	 *
	 * @var   Field[]
	 *
	 * @since  1.7.0
	 */
	protected $fields = array();

	/**
	 * The active variant id
	 *
	 * @var   int
	 *
	 * @since  1.7.0
	 */
	public $active;

	/**
	 * Bind a variant item
	 *
	 * @param   Variant  $variant
	 * @param   bool     $active
	 *
	 * @return  void
	 *
	 * @since  1.7.0
	 */
	public function addVariant(Variant $variant, $active = false)
	{
		foreach ($this->fields as $field)
		{
			$this->processValues($variant, $field);
		}

		$this->variants[$variant->id] = $variant;

		if ($active)
		{
			$this->active = $variant->id;
		}
	}

	/**
	 * Get a bound variant item
	 *
	 * @param   int  $vid
	 *
	 * @return  Variant
	 *
	 * @since  1.7.0
	 */
	public function getVariant($vid)
	{
		if (isset($this->variants[$vid]))
		{
			return $this->variants[$vid];
		}

		return null;
	}

	/**
	 * Bind an attribute field
	 *
	 * @param   Field  $field
	 *
	 * @return  void
	 *
	 * @since  1.7.0
	 */
	public function addField(Field $field)
	{
		foreach ($this->variants as $variant)
		{
			$this->processValues($variant, $field);
		}

		$this->fields[] = $field;
	}

	/**
	 * Find items from the list of bound variants matching the specifications
	 *
	 * @param   array  $values
	 * @param   bool   $checkStock
	 *
	 * @return  Variant[]
	 *
	 * @since  1.7.0
	 */
	public function filterVariants(array $values, $checkStock = false)
	{
		$variants = array();

		foreach ($this->variants as $variant)
		{
			if ($checkStock && $variant->stock <= 0)
			{
				continue;
			}

			foreach ($values as $key => $value)
			{
				$sp = ArrayHelper::getValue($variant->specification, $key);

				$sp    = (array) $sp;
				$value = (array) $value;

				if (!array_intersect($value, $sp))
				{
					continue 2;
				}
			}

			$variants[] = $variant;
		}

		return $variants;
	}

	/**
	 * Get a list of visible fields
	 *
	 * @return  Field[]
	 *
	 * @since  1.7.0
	 */
	public function getVisibleFields()
	{
		$fields = array();

		foreach ($this->fields as $field)
		{
			if ($field->isVisible())
			{
				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Get the specification value for the given variant and field id.
	 * If variantId is omitted the active variant will be used.
	 *
	 * @param   int  $fieldId
	 * @param   int  $variantId
	 *
	 * @return  Field[]
	 *
	 * @since  1.7.0
	 */
	public function getValue($fieldId, $variantId = null)
	{
		$variant = $this->getVariant($variantId ?: $this->active);

		if ($variant && isset($variant->specification[$fieldId]))
		{
			return $variant->specification[$fieldId];
		}

		return null;
	}

	/**
	 * Match the specification value for the given variant and field id against given value.
	 * If variantId is omitted the active variant will be used.
	 *
	 * @param   mixed  $value
	 * @param   int    $fieldId
	 * @param   int    $variantId
	 *
	 * @return  bool
	 *
	 * @since  1.7.0
	 */
	public function matchValue($value, $fieldId, $variantId = null)
	{
		$sp = $this->getValue($fieldId, $variantId);

		$sp    = (array) $sp;
		$value = (array) $value;

		return array_intersect($value, $sp) ? true : false;
	}

	/**
	 * Check whether the given field option is available with other filters intact
	 *
	 * @param   Field        $field
	 * @param   FieldOption  $option
	 *
	 * @return  bool
	 *
	 * @since  1.7.0
	 */
	public function isAvailable(Field $field, FieldOption $option)
	{
		$variant = $this->getVariant($this->active);

		if ($variant)
		{
			$opts = $variant->specification;

			$opts[$field->id] = $option->value;

			$items = $this->filterVariants($opts);

			if (count($items))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get identifier to an available variant
	 *
	 * @param   Field        $field
	 * @param   FieldOption  $option
	 *
	 * @return  Variant
	 *
	 * @since  1.7.0
	 */
	public function getAvailableVariant(Field $field, FieldOption $option)
	{
		$variant = $this->getVariant($this->active);

		if ($variant)
		{
			$opts = $variant->specification;

			$opts[$field->id] = $option->value;

			$items = $this->filterVariants($opts);

			if (count($items))
			{
				return reset($items);
			}

			$opts = array($field->id => $option->value);

			$items = $this->filterVariants($opts);

			if (count($items))
			{
				return reset($items);
			}
		}

		return null;
	}

	/**
	 * Process variant specification to assign as the field option
	 *
	 * @param   Variant  $variant
	 * @param   Field    $field
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function processValues(Variant $variant, Field $field)
	{
		$helper = SellaciousHelper::getInstance();
		$value  = ArrayHelper::getValue($variant->specification, $field->id);

		if (isset($value))
		{
			if (is_scalar($value))
			{
				if (strlen($value))
				{
					$option = $field->getOption($value, true);

					$option->addVariant($variant);
				}
			}
			elseif (is_array($value))
			{
				// Process only if all elements in array are scalar, i.e. simple multi-valued list
				$sv = array_filter($value, 'is_scalar');

				if (count($sv) === count($value))
				{
					foreach ($value as $v)
					{
						if (strlen($v))
						{
							$option = $field->getOption($v, true);

							$option->addVariant($variant);
						}
					}
				}
			}
			// Special condition for unitcombo field
			elseif (is_object($value) && !empty($value) && $field->type == 'unitcombo')
			{
				// Process it as a string
				$value  = $helper->unit->explain($value, true);
				$option = $field->getOption($value, true);
				$option->addVariant($variant);
			}
		}
	}
}
