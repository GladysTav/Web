<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

namespace Sellacious\Template;

// no access
use Exception;
use Sellacious\Media\Attachment;

defined('_JEXEC') or die;

/**
 * @package  Sellacious\Template
 *
 * @since    2.0.0
 */
class OrderPaymentSuccessNotificationTemplate extends OrderNotificationTemplate
{
	/**
	 * Method to get the name of this template object. Must be unique for each context
	 *
	 * @return  string
	 *
	 * @since   2.0.0
	 */
	public function getName()
	{
		return 'order_payment_success';
	}

	/**
	 * Get a list of attachments if applicable
	 *
	 * @param   string  $type  The recipient type so that we have the flexibility to
	 *                         support different attachment for different recipient type on same event
	 *
	 * @return  Attachment[]
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	public function getAttachments($type = null)
	{
		$filter = array('table_name' => 'orders', 'record_id' => $this->order->get('id'), 'list.select' => 'a.path, a.original_name name');
		$files  = $this->helper->media->loadObjectList($filter);

		$attachments = parent::getAttachments($type);

		if (!empty($files))
		{
			foreach ($files as $file)
			{
				$attachments[] = new Attachment($file['path'], $file['name']);
			}
		}

		return $attachments;
	}

}
