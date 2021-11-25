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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since	2.0.0
 */
class JFormFieldVariants extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'variants';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @throws  \Exception
	 *
	 * @since   2.0.0
	 */
	protected function getInput()
	{
		$variants  = array();
		$prices    = array();
		$productId = $this->element['product_id'] ? (int) $this->element['product_id'] : 0;
		$sellerUid = $this->element['seller_uid'] ? (int) $this->element['seller_uid'] : 0;

		if (!empty($this->value))
		{
			$this->value = is_array($this->value) ? $this->value : ArrayHelper::fromObject($this->value);
			$variants    = ArrayHelper::getValue($this->value, 'items');
			$prices      = ArrayHelper::getValue($this->value, 'prices');
		}

		$data = array('variants' => $variants, 'prices' => $prices, 'product_id' => $productId, 'seller_uid' => $sellerUid);
		$html = JLayoutHelper::render('com_sellacious.formfield.variants', $data);

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   2.0.0
	 */
	public function getLabel()
	{
		return '';
	}
}
