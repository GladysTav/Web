<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Message;

// no direct access.
defined('_JEXEC') or die;

use JHtml;
use JRoute;
use JText;
use Sellacious\Product;

/**
 * Message Reference for Product
 *
 * @since   2.0.0
 */
class ProductReference extends AbstractReference
{
	/**
	 * Method to get message reference as text
	 *
	 * @return   string
	 *
	 * @since    2.0.0
	 */
	public function asText()
	{
		$text = '';

		if ($this->helper->product->parseCode($this->value, $product_id, $variant_id, $seller_uid))
		{
			$product = new Product($product_id, $variant_id, $seller_uid);
			$url     = JRoute::_('index.php?option=com_sellacious&view=product&p=' . $this->value, false);
			$text    = JText::_('COM_SELLACIOUS_MESSAGE_REFERENCE_TEXT') . JHtml::_('link', $url, $product->get('title'));
		}

		return $text;
	}
}
