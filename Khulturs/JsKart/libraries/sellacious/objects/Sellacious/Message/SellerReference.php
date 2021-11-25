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

/**
 * Message Reference for Product
 *
 * @since   2.0.0
 */
class SellerReference extends AbstractReference
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
		$text   = '';
		$seller = $this->helper->seller->getItem(array('user_id' => $this->value));

		if ($seller)
		{
			$title = $seller->store_name ?: $seller->title;
			$url   = JRoute::_('index.php?option=com_sellacious&view=store&id=' . $this->value, false);
			$text  = JText::_('COM_SELLACIOUS_MESSAGE_REFERENCE_TEXT') . JHtml::_('link', $url, $title);
		}

		return $text;
	}
}
